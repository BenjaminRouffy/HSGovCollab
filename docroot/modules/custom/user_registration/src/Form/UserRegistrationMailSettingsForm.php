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

}
