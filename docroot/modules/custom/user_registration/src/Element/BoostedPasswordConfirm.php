<?php

namespace Drupal\user_registration\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\PasswordConfirm;

/**
 * Provides a boosted confirm password.
 *
 * @see \Drupal\Core\Render\Element\PasswordConfirm
 *
 * @FormElement("boosted_password_confirm")
 */
class BoostedPasswordConfirm extends PasswordConfirm {

  /**
   * Expand a password_confirm field into two text boxes.
   */
  public static function processPasswordConfirm(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = parent::processPasswordConfirm($element, $form_state, $complete_form);

    $element['pass1']['#title'] = t('New password');
    $element['pass2']['#title'] = t('Confirm new password');

    return $element;
  }

}
