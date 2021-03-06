<?php

namespace Drupal\email_confirm\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a form element for double-input of e-mail.
 *
 * @see \Drupal\Core\Render\Element\PasswordConfirm
 * @see \Drupal\Core\Render\Element\Email
 *
 * @FormElement("email_confirm")
 */
class EmailConfirm extends FormElement   {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#input' => TRUE,
      '#markup' => '',
      '#process' => array(
        array($class, 'processEmailConfirm'),
      ),
      '#theme_wrappers' => array('form_element'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      $element += ['#default_value' => []];
      return (array) $element['#default_value'] + ['mail1' => '', 'mail2' => ''];
    }
    $value = ['mail1' => '', 'mail2' => ''];
    // Throw out all invalid array keys; we only allow mail1 and mail2.
    foreach ($value as $allowed_key => $default) {
      // These should be strings, but allow other scalars since they might be
      // valid input in programmatic form submissions. Any nested array values
      // are ignored.
      if (isset($input[$allowed_key]) && is_scalar($input[$allowed_key])) {
        $value[$allowed_key] = (string) $input[$allowed_key];
      }
    }
    return $value;
  }

  /**
   * Expand a email_confirm field into two text boxes.
   */
  public static function processEmailConfirm(&$element, FormStateInterface $form_state, &$complete_form) {
    $user = \Drupal::currentUser();
    $account = $form_state->getFormObject()->getEntity();

    $element['mail1'] = array(
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => $element['#required'],
      '#attributes' => [
        'class' => ['email-field', 'js-email-field'],
        'placeholder' => t('Please enter your email address'),
      ],
      '#error_no_message' => TRUE,
    );
    $element['mail2'] = array(
      '#type' => 'email',
      '#title' => t('Confirm email'),
      '#required' => $element['#required'],
      '#attributes' => [
        'class' => ['email-confirm', 'js-email-confirm'],
        'placeholder' => t('Retype your email address'),
      ],
      '#error_no_message' => TRUE,
    );

    if (!$account->isAnonymous()) {
      $email_info = self::getUserMailInfo($account);
      $current_email = $email_info['name'];
      $current_domain = $email_info['domain'];

      if ($user->id() == $account->id() && !$user->hasPermission('administer users')) {
        $element['mail1']['#type'] = 'textfield';
        $element['mail1']['#default_value'] = $current_email;
        $element['mail1']['#field_suffix'] = '<span class="domain-name">' . $current_domain . '</span>';
        $element['mail2']['#type'] = 'textfield';
        $element['mail2']['#default_value'] = $current_email;
        $element['mail2']['#field_suffix'] = '<span class="domain-name">' . $current_domain . '</span>';
      }
      elseif ($user->hasPermission('administer users')) {
        $element['mail1']['#default_value'] = $email_info['full'];
        $element['mail2']['#default_value'] = $email_info['full'];
      }
    }
    else {
      if (!empty($element['#value'][0])) {
        $element['mail1']['#default_value'] = $element['#value'][0];
      }
    }

    $element['#element_validate'] = array(array(get_called_class(), 'validateEmailConfirm'));
    $element['#tree'] = TRUE;

    if (isset($element['#size'])) {
      $element['mail1']['#size'] = $element['mail2']['#size'] = $element['#size'];
    }

    return $element;
  }

  /**
   * Validates a email_confirm element.
   */
  public static function validateEmailConfirm(&$element, FormStateInterface $form_state, &$complete_form) {
    $account = $form_state->getFormObject()->getEntity();
    $user = \Drupal::currentUser();

    $mail1 = trim($element['mail1']['#value']);
    $mail2 = trim($element['mail2']['#value']);

    if (strlen($mail1) > 0 || strlen($mail2) > 0) {
      if (strcmp($mail1, $mail2)) {
        $form_state->setError($element, t('The specified emails do not match.'));
      }
    }
    elseif ($element['#required'] && $form_state->getUserInput()) {
      $form_state->setError($element, t('Email field is required.'));
    }

    // Email field must be converted from a two-element array into a single
    // string regardless of validation results.
    $form_state->setValueForElement($element['mail1'], NULL);
    $form_state->setValueForElement($element['mail2'], NULL);

    if ($account->isAnonymous() || $user->hasPermission('administer users')) {
      $form_state->setValueForElement($element, $mail1);
    }
    else {
      $email_info = self::getUserMailInfo($account);
      if ($mail1 != $email_info['name']) {
        $email_check = (bool) filter_var($mail1 . $email_info['domain'], FILTER_VALIDATE_EMAIL);
        if (!$email_check) {
          $form_state->setError($element, t('The email address %mail is not valid.', [
            '%mail' => $mail1 . $email_info['domain'],
          ]));
        }
      }
      $form_state->setValueForElement($element, $mail1 . $email_info['domain']);
    }

    return $element;
  }

  /**
   * Function to get user email info.
   *
   * @param object $user
   *   User entity.
   *
   * @return array
   *   Return array with email name and email domain.
   */
  public static function getUserMailInfo($user) {
    $email_info = [
      'name' => '',
      'domain' => '',
      'full' => '',
    ];

    if (!empty($user)) {
      $full_email = $user->getEmail();

      if (!empty($full_email)) {
        $user_email = explode('@', $full_email);
        $email_info = [
          'name' => $user_email[0],
          'domain' => '@' . $user_email[1],
          'full' => $full_email,
        ];
      }
    }

    return $email_info;
  }

}
