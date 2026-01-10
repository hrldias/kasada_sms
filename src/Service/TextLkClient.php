use Drupal\Core\Config\ConfigFactoryInterface;

class TextLkClient {

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
}