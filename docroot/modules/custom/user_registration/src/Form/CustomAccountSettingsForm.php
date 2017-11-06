<?php

namespace Drupal\user_registration\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\AccountSettingsForm;

/**
 * Extends core Account settings form in order to add init mail configuration
 */
class CustomAccountSettingsForm extends AccountSettingsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_registration_custom_account_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'system.site',
      'user.mail',
      'user.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Email configuration for initial user setup has been added.
    $form = parent::buildForm($form, $form_state);
    $mail_config = $this->config('user.mail');

    $form['email_user_init'] = [
      '#type' => 'details',
      '#title' => $this->t('Initial User Setup'),
      '#description' => $this->t('Edit initial user setup mail message'),
      '#group' => 'email',
      '#weight' => 10,
    ];
    $form['email_user_init']['email_user_init_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $mail_config->get('email_user_init.subject'),
      '#maxlength' => 180,
    ];
    $form['email_user_init']['email_user_init_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => $mail_config->get('email_user_init.body'),
      '#rows' => 12,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('user.mail')
      ->set('email_user_init.body', $form_state->getValue('email_user_init_body'))
      ->set('email_user_init.subject', $form_state->getValue('email_user_init_subject'))
      ->save();
  }

}
