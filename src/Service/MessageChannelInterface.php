<?php
namespace Drupal\kasada_sms\Service;

interface MessageChannelInterface {
  /**
   * @throws \Exception on failure
   */
  public function send(string $recipient, string $message): bool;
}