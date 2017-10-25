<?php

namespace Drupal\notification_customizations\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceValidateInterface;

/**
 * Alter.
 */
class UserAdminSettingsFormAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface, FormAlterServiceSubmitInterface, FormAlterServiceValidateInterface {
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
    $site_email = \Drupal::config('system.site')->get('mail');
    $form['mail_notification_address']['#title'] = $this->t('Main administration notification email address');
    $form['mail_notification_address']['#description'] = $this->t("The email address to be used as the 'from' address for all account notifications listed below is <em>%site-email</em>. If <em>'Visitors, but administrator approval is required'</em> is selected above, a notification email will be sent to this address for any new registrations. Leave empty to use the default system email address <em>(%site-email).</em>", ['%site-email' => $site_email]);
    $form['registration_cancellation']['admin_mail_notification_addresses'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Additional notification administration email addresses'),
      '#default_value' => \Drupal::config('notification_customizations.settings')->get('notification_customizations.settings.emails'),
      '#description' => $this->t("If <em>'Visitors, but administrator approval is required'</em> is selected above, a notification email will also be sent to this addresses for any new registrations. Please enter one email per line", ['%site-email' => $site_email]),
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function formSubmit(&$form, FormStateInterface $form_state) {
    $mail_config = \Drupal::configFactory()->getEditable('notification_customizations.settings');
    $emails = $form_state->getValue('admin_mail_notification_addresses');
    $mail_config->set('notification_customizations.settings.emails', trim($emails))->save();
  }

  /**
   * @inheritdoc
   */
  public function formValidate(&$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if (isset($values['admin_mail_notification_addresses']) && !empty($values['admin_mail_notification_addresses'])) {
      $raw_emails = trim($values['admin_mail_notification_addresses']);
      $array_emails = preg_split('/\r\n?|\n/', $raw_emails);
      $approved_emails = array_filter($array_emails);
      $wrong_emails = [];

      foreach ($approved_emails as $email) {
        $email_check = (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email_check) {
          $wrong_emails[] = $email;
        }
      }

      if ((bool) count($wrong_emails)) {
        $form_state->setErrorByName('admin_mail_notification_addresses', $this->t('Those email %mail are not valid or have empty spaces after email.', ['%mail' => implode(", ", $wrong_emails)]));
      }
    }
  }
}
