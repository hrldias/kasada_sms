namespace Drupal\kasada_sms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class KasadaSmsSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['kasada_sms.settings'];
  }

  public function getFormId() {
    return 'kasada_sms_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('kasada_sms.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text.lk API key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['sender_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sender ID'),
      '#default_value' => $config->get('sender_id'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('kasada_sms.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('sender_id', $form_state->getValue('sender_id'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}