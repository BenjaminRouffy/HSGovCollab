<?php

namespace Drupal\user_registration\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Form\UserPasswordForm;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Extends core Account settings form in order to add init mail configuration
 */
class CustomUserPasswordForm extends UserPasswordForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_registration_custom_user_password';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    if (!empty($form['name'])) {
      $form['name']['#title'] = t('E-mail');
    }
    unset($form['mail']);
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $route_name = \Drupal::request()->attributes->get(RouteObjectInterface::ROUTE_NAME);
    if ($route_name === 'page_manager.page_view_user_password_init') {

      $op = 'email_user_init';
      $account = $form_state->getValue('account');
      $params['account'] = $account;
      $langcode = $account->getPreferredLangcode();
      // Get the custom site notification email to use as the from email address
      // if it has been set.
      $site_mail = \Drupal::config('system.site')->get('mail_notification');
      if (empty($site_mail)) {
        $site_mail = \Drupal::config('system.site')->get('mail');
      }
      if (empty($site_mail)) {
        $site_mail = ini_get('sendmail_from');
      }

      $mail = \Drupal::service('plugin.manager.mail')->mail('user_registration', $op, $account->getEmail(), $langcode, $params, $site_mail);
      if (!empty($mail)) {
        $this->logger('user')->notice('User initial setup instructions mailed to %name at %email.', ['%name' => $account->getUsername(), '%email' => $account->getEmail()]);
        drupal_set_message($this->t('Further instructions have been sent to your email address.'));
      }

      $redirect_page = \Drupal::config('system.site')->get('page');
      if (!empty($redirect_page['init_user_redirect_page'])) {
        $form_state->setRedirectUrl(Url::fromUserInput($redirect_page['init_user_redirect_page']));

      }
      else {
        $form_state->setRedirect('user.page');
      }

    }
    else {
      parent::submitForm($form, $form_state);
    }

  }

}
