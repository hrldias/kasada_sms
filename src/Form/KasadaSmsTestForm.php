<?php

namespace Drupal\kasada_sms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class KasadaSmsTestForm extends FormBase {

  public function getFormId(): string {
    return 'kasada_sms_test_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['mobile'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile number'),
      '#description' => $this->t('Sri Lankan format. Example: 0712345678 or 94712345678'),
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Test message'),
      '#default_value' => 'Test SMS from Kasada',
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send test SMS'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $mobile = $form_state->getValue('mobile');
    $message = $form_state->getValue('message');

    try {
      /** @var \Drupal\kasada_sms\Service\TextLkClient $sms */
      $sms = \Drupal::service('kasada_sms.textlk');

      if ($sms->send($mobile, $message)) {
        $this->messenger()->addStatus(
          $this->t('Test SMS sent successfully to @mobile.', ['@mobile' => $mobile])
        );
      }
      else {
        $this->messenger()->addError(
          $this->t('SMS sending failed. Check logs for details.')
        );
      }
    }
    catch (\Exception $e) {
      $this->messenger()->addError(
        $this->t('Error sending SMS: @msg', ['@msg' => $e->getMessage()])
      );
    }
  }
}