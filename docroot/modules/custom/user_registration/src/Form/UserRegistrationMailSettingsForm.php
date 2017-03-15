<?php

namespace Drupal\user_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Mail Settings settings for this site.
 */
class UserRegistrationMailSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_registration_mail_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['user_registration.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('user_registration.settings');

    $form['general']['user_registration_pattern'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail Settings pattern'),
      '#default_value' => $config->get('pattern'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('user_registration.settings');

    if ($form['general']['user_registration_pattern']['#default_value'] != $form_state->getValue('user_registration_pattern')) {
      $config->set('pattern', $form_state->getValue('user_registration_pattern'))->save();
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email_list = str_replace(["\r\n", "\n", "\n\r"], "|", trim($form_state->getValue('user_registration_pattern')));
    $email_list = explode("|", $email_list);
    $email_list = array_filter($email_list);
    $wrong_emails = [];

    foreach ($email_list as $email) {
      $pattern = '/^[\@](?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/is';

      if (!preg_match($pattern, $email)) {
        $wrong_emails[] = $email;
      }
    }

    if ((bool) count($wrong_emails)) {
      $form_state->setErrorByName('user_registration_pattern', $this->t('Those email domains %mail are not valid.', ['%mail' => implode(", ", $wrong_emails)]));
    }
  }

}
