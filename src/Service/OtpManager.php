<?php
namespace Drupal\kasada_sms\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;

use Drupal\kasada_sms\Service\MessageChannelInterface;

class OtpManager {

  protected Connection $db;
  
  protected AccountProxyInterface $currentUser;

  protected MessageChannelInterface $sms;
  protected MessageChannelInterface $whatsapp;

  public function __construct(
    MessageChannelInterface $sms,
    MessageChannelInterface $whatsapp,
    Connection $db,
    AccountProxyInterface $current_user
  ) {
    $this->sms = $sms;
    $this->whatsapp = $whatsapp;
    $this->db = $db;
    $this->currentUser = $current_user;
  }

  public function sendOtp(string $mobile, string $channel = 'sms'): void {
  $this->assertRateLimit();

  $otp = random_int(100000, 999999);

  $this->storeOtp($mobile, $otp);

  $message = "Your OTP is {$otp}";

  $sender = match ($channel) {
    'whatsapp' => $this->whatsapp,
    default => $this->sms,
  };

  if (!$sender->send($mobile, $message)) {
    throw new \RuntimeException('OTP delivery failed.');
  }
}

  public function verifyOtp(string $mobile, string $otp): bool {
    $hash = hash('sha256', $otp);

    $record = $this->db->select('kasada_sms_otp', 'o')
      ->fields('o')
      ->condition('mobile', $mobile)
      ->condition('otp_hash', $hash)
      ->condition('verified', 0)
      ->condition('expires', time(), '>')
      ->execute()
      ->fetch();

    if (!$record) {
      return FALSE;
    }

    $this->db->update('kasada_sms_otp')
      ->fields(['verified' => 1])
      ->condition('id', $record->id)
      ->execute();

    return TRUE;
  }
}