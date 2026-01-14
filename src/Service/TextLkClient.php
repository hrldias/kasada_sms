<?php

namespace Drupal\kasada_sms\Service;

use Drupal\kasada_sms\Service\MessageChannelInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;



class TextLkClient implements MessageChannelInterface {

  protected ClientInterface $httpClient;
  protected string $apiKey;
  protected string $senderId;

  public function __construct(
    ClientInterface $http_client,
    ConfigFactoryInterface $config_factory
  ) {
    $config = $config_factory->get('kasada_sms.settings');

    $this->httpClient = $http_client;
    $this->apiKey = $config->get('api_key');
    $this->senderId = $config->get('sender_id');
  }

  protected function normalizeMobile(string $mobile): string {
  $mobile = preg_replace('/\D+/', '', $mobile);

  if (str_starts_with($mobile, '0')) {
    $mobile = '94' . substr($mobile, 1);
  }

  if (!preg_match('/^94\d{9}$/', $mobile)) {
    throw new \InvalidArgumentException('Invalid Sri Lankan mobile number.');
  }

  return $mobile;
}

  public function send(string $mobile, string $message): bool
  {
    try {
      $mobile = $this->normalizeMobile($mobile);
      
      $response = $this->httpClient->post(
        'https://app.text.lk/api/http/sms/send',
        [
          'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
          ],
          'json' => [
            'api_token' => $this->apiKey,
            'recipient' => $mobile,
            'sender_id' => $this->senderId,
            'type' => 'plain',
            'message' => $message,
          ],
          'timeout' => 10,
        ]
      );

      $data = json_decode($response->getBody()->getContents(), TRUE);

      if (!empty($data['status']) && $data['status'] === 'success') {
        \Drupal::logger('kasada_sms')->info('SMS sent to @mobile', [
          '@mobile' => $mobile,
        ]);
        return TRUE;
      }

      \Drupal::logger('kasada_sms')->error('Text.lk SMS error: @msg', [
        '@msg' => $data['message'] ?? 'Unknown error',
      ]);
      return FALSE;
    } catch (\Throwable $e) {
      \Drupal::logger('kasada_sms')->error('Text.lk exception: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }
}
