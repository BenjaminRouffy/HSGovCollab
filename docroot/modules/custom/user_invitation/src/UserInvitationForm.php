<?php

namespace Drupal\user_invitation;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\formblock\Plugin\Block\UserRegisterBlock;
use Drupal\user\RegisterForm;
use Drupal\user_registration\UserRegistrationRegister;

/**
 * Class ModuleHandlerAlterRegister.
 */
class UserInvitationForm extends UserRegistrationRegister {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Start with the default user account fields.
    $form = parent::form($form, $form_state);

    $form['account']['pass']['#access'] = FALSE;
    $form['account']['pass']['#required'] = FALSE;

    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $form_state->setValue('notify', TRUE);
    parent::save($form, $form_state);
  }

}
