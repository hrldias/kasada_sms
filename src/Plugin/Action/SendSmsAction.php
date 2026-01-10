<?php 
namespace Drupal\kasada_sms\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\kasada_sms\Service\TextLkClient;

/**
 * Sends an SMS using text.lk.
 *
 * @Action(
 *   id = "kasada_send_sms",
 *   label = @Translation("Send SMS (text.lk)"),
 *   type = "entity"
 * )
 */
class SendSmsAction extends ActionBase {

  protected TextLkClient $sms;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, TextLkClient $sms) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->sms = $sms;
  }

  public static function create($container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('kasada_sms.textlk')
    );
  }

  public function execute($entity = NULL) {
    $mobile = $this->configuration['mobile'];
    $message = $this->configuration['message'];

    if ($mobile && $message) {
      $this->sms->send($mobile, $message);
    }
  }

  public function defaultConfiguration() {
    return [
      'mobile' => '',
      'message' => '',
    ];
  }

  public function buildConfigurationForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['mobile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile number'),
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
    ];

    return $form;
  }

}