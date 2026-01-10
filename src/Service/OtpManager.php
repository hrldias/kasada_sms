namespace Drupal\kasada_sms\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;

class OtpManager {

  protected Connection $db;
  protected TextLkClient $sms;
  protected AccountProxyInterface $currentUser;

  public function __construct(Connection $db, TextLkClient $sms, AccountProxyInterface $current_user) {
    $this->db = $db;
    $this->sms = $sms;
    $this->currentUser = $current_user;
  }

  public function sendOtp(string $mobile): void {
    $otp = random_int(100000, 999999);

    $this->db->insert('kasada_sms_otp')->fields([
      'uid' => $this->currentUser->id(),
      'mobile' => $mobile,
      'otp_hash' => hash('sha256', $otp),
      'created' => time(),
      'expires' => time() + 300,
    ])->execute();

    $this->sms->send($mobile, "Your OTP is $otp");
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