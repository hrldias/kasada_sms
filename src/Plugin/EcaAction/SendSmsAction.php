<?php

namespace Drupal\kasada_sms\Plugin\EcaAction;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ActionBase;

/**
 * Provides an ECA action to send SMS via text.lk.
 *
 * @EcaAction(
 *   id = "kasada_send_sms",
 *   label = @Translation("Send SMS (Kasada)"),
 *   description = @Translation("Sends an SMS message using text.lk gateway"),
 * )
 */
class SendSmsAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $mobile = $this->tokenService->replace($this->configuration['mobile']);
    $message = $this->tokenService->replace($this->configuration['message']);

    if ($mobile && $message) {
      try {
        /** @var \Drupal\kasada_sms\Service\TextLkClient $sms_client */
        $sms_client = \Drupal::service('kasada_sms.textlk');
        if ($sms_client->send($mobile, $message)) {
          \Drupal::logger('kasada_sms')->info('SMS sent to @mobile', ['@mobile' => $mobile]);
        } else {
          \Drupal::logger('kasada_sms')->warning('SMS send returned false for @mobile', ['@mobile' => $mobile]);
        }
      } catch (\Exception $e) {
        \Drupal::logger('kasada_sms')->error('Failed to send SMS to @mobile: @error', [
          '@mobile' => $mobile,
          '@error' => $e->getMessage(),
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['mobile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile number'),
      '#description' => $this->t('You can use tokens like [user:field_phone] or [node:field_phone]'),
      '#default_value' => $this->configuration['mobile'] ?? '',
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#description' => $this->t('You can use tokens like [user:name], [node:title], etc.'),
      '#default_value' => $this->configuration['message'] ?? '',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'mobile' => '',
      'message' => '',
    ] + parent::defaultConfiguration();
  }

}
