<?php

namespace Drupal\user_registration;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\formblock\Plugin\Block\UserRegisterBlock;
use Drupal\user\RegisterForm;

/**
 * Class ModuleHandlerAlterRegister.
 *
 * @package Drupal\module_handler_alter
 */
class UserRegistrationRegister extends RegisterForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    // Start with the default user account fields.
    $form = parent::form($form, $form_state);

    foreach (Element::children($form['account']) as $children) {
      if(isset($form['account'][$children]['#description'])) {
        unset($form['account'][$children]['#description']);
      }
    }
    $form['account']['mail']['#type'] = 'email_confirm';

    // @TODO Generate user name by email_address.
    // $form['account']['name']['#access'] = FALSE;
    // $form['account']['name']['#default_value'] = sprintf('User%s', rand(1000,9999));


    foreach ($form['field_organisation']['widget']['#options'] as $key => $option) {
      if(is_array($option)) {
        $form['field_organisation']['widget']['#options'] += $option;
        unset($form['field_organisation']['widget']['#options'][$key]);
      }
    }
    $form['field_person_titles']['widget']['#options']['_none'] = $this->t('Select title');
    $form['field_organisation']['widget']['#options']['_none'] = $this->t('Select organisation');

    $states = array(
      'visible' => array(
        // @TODO Change this logic.
        'select[name="field_organisation"]' => array('value' => 485),
      ),
    );
    $form['field_non_member_organization']['#states'] = $states;


    $account = $this->getEntity();
    $user = $this->currentUser();
    $config = \Drupal::config('user.settings');


    $register = $account->isAnonymous();
    $admin = $user->hasPermission('administer users');

    if ($admin || !$register) {
      $status = $account->get('status')->value;
    }
    else {
      $status = $config->get('register') == USER_REGISTER_VISITORS && $mail_match = TRUE ? 1 : 0;
    }

    $form['account']['status'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Status'),
      '#default_value' => $status,
      '#options' => array($this->t('Blocked'), $this->t('Active')),
      '#access' => $admin,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $account = $this->entity;
    $pass = $account->getPassword();
    $admin = $form_state->getValue('administer_users');
    $notify = !$form_state->isValueEmpty('notify');

    // Save has no return value so this cannot be tested.
    // Assume save has gone through correctly.
    $account->save();

    $form_state->set('user', $account);
    $form_state->setValue('uid', $account->id());

    $this->logger('user')->notice('New user: %name %email.', array('%name' => $form_state->getValue('name'), '%email' => '<' . $form_state->getValue('mail') . '>', 'type' => $account->link($this->t('Edit'), 'edit-form')));

    // Add plain text password into user account to generate mail tokens.
    $account->password = $pass;

    // New administrative account without notification.
    if ($admin && !$notify) {
      drupal_set_message($this->t('Created a new user account for <a href=":url">%name</a>. No email has been sent.', array(':url' => $account->url(), '%name' => $account->getUsername())));
    }
    // No email verification required; log in user immediately.
    elseif (!$admin && !\Drupal::config('user.settings')->get('verify_mail') && $account->isActive()) {
      _user_mail_notify('register_no_approval_required', $account);
      user_login_finalize($account);
      drupal_set_message($this->t('Registration successful. You are now logged in.'));
      $form_state->setRedirect('<front>');
    }
    // No administrator approval required.
    elseif ($account->isActive() || $notify) {
      if (!$account->getEmail() && $notify) {
        drupal_set_message($this->t('The new user <a href=":url">%name</a> was created without an email address, so no welcome message was sent.', array(':url' => $account->url(), '%name' => $account->getUsername())));
      }
      else {
        $op = $notify ? 'register_admin_created' : 'register_no_approval_required';
        if (_user_mail_notify($op, $account)) {
          if ($notify) {
            drupal_set_message($this->t('A welcome message with further instructions has been emailed to the new user <a href=":url">%name</a>.', array(':url' => $account->url(), '%name' => $account->getUsername())));
          }
          else {
            drupal_set_message($this->t('A welcome message with further instructions has been sent to your email address.'));
            $form_state->setRedirect('<front>');
          }
        }
      }
    }
    // Administrator approval required.
    else {
      _user_mail_notify('register_pending_approval', $account);
      drupal_set_message($this->t('Thank you for applying for an account. Your account is currently pending approval by the site administrator.<br />In the meantime, a welcome message with further instructions has been sent to your email address.'));
      $form_state->setRedirect('<front>');
    }
  }

}