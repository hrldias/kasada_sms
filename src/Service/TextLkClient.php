<?php
namespace Drupal\kasada_sms\Service;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class TextLkClient {

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

  public function send(string $mobile, string $message): bool {
    $response = $this->httpClient->post('https://api.text.lk/send', [
      'json' => [
        'apikey' => $this->apiKey,
        'sender_id' => $this->senderId,
        'to' => $mobile,
        'message' => $message,
      ],
      'timeout' => 5,
    ]);

    return $response->getStatusCode() === 200;
  }
}