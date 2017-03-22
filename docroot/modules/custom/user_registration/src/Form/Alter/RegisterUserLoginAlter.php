<?php

namespace Drupal\user_registration\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;

/**
 * Class RegisterUserLoginAlter.
 */
class RegisterUserLoginAlter implements FormAlterServiceBaseInterface, FormAlterServiceSubmitInterface {

  /**
   * Checks that form is matched to specific conditions.
   *
   * @return bool
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * Form submit custom implementation.
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formSubmit(&$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl(new Url('<front>'));
  }

}
