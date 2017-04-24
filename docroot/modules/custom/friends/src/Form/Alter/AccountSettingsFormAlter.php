<?php

namespace Drupal\friends\Form\Alter;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alter.
 */
class AccountSettingsFormAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface, FormAlterServiceSubmitInterface {
  use StringTranslationTrait;

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    $mail_config = \Drupal::config('user.mail');

    $form['friend_approve_admin'] = [
      '#type' => 'details',
      '#title' => $this->t('Friend approve admin'),
      '#group' => 'email',
    ];
    $form['friend_approve_admin']['friend_approve_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#default_value' => $mail_config->get('friend_approve.subject'),
      '#maxlength' => 180,
    ];
    $form['friend_approve_admin']['friend_approve_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => $mail_config->get('friend_approve.body'),
      '#rows' => 8,
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function formSubmit(&$form, FormStateInterface $form_state) {
    $mail_config = \Drupal::configFactory()->getEditable('user.mail');

    $mail_config->set('friend_approve.subject', $form_state->getValue('friend_approve_subject'))
      ->set('friend_approve.body', $form_state->getValue('friend_approve_body'))
      ->save();
  }
}
