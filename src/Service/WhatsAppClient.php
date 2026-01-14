<?php

namespace Drupal\kasada_sms\Service;

use Drupal\kasada_sms\Service\MessageChannelInterface;

class WhatsAppClient implements MessageChannelInterface {

  public function send(string $recipient, string $message): bool {
    // For now: stub
    // Later: Meta Cloud API call

    \Drupal::logger('kasada_sms')->info(
      'WhatsApp message queued for @recipient',
      ['@recipient' => $recipient]
    );

    return TRUE;
  }
}