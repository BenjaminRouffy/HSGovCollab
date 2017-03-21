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
    $registered = $account->isAnonymous();

    if ($registered) {
      $element['mail1'] = array(
        '#type' => 'email',
        '#title' => t('Email'),
        '#value' => empty($element['#value']['mail1']) ? (!empty($element['#default_value']) ? $element['#default_value'] : NULL) : $element['#value']['mail1'],
        '#required' => $element['#required'],
        '#attributes' => [
          'class' => ['email-field', 'js-email-field'],
          'placeholder' => t('Enter your email'),
        ],
        '#error_no_message' => TRUE,
      );
      $element['mail2'] = array(
        '#type' => 'email',
        '#title' => t('Confirm email'),
        '#value' => empty($element['#value']['mail2']) ? (!empty($element['#default_value']) ? $element['#default_value'] : NULL) : $element['#value']['mail2'],
        '#required' => $element['#required'],
        '#attributes' => [
          'class' => ['email-confirm', 'js-email-confirm'],
          'placeholder' => t('Retype your email address'),
        ],
        '#error_no_message' => TRUE,
      );
      unset($element['#default_value']);
    }
    else {
      $email_info = self::getUserMailInfo($account);
      $current_email = $email_info['name'];
      $current_domain = $email_info['domain'];

      if ($user->id() == $account->id() || $user->hasPermission('administer users')) {
        $element['mail1'] = array(
          '#type' => 'textfield',
          '#title' => t('Email'),
          '#default_value' => (!$registered ? $current_email : ''),
          '#required' => $element['#required'],
          '#attributes' => [
            'class' => ['email-field', 'js-email-field'],
            'placeholder' => t('Enter your email'),
          ],
          '#field_suffix' => '<span class="domain-name">' . (!$registered ? $current_domain : '') . '</span>',
          '#error_no_message' => TRUE,
        );
        $element['mail2'] = array(
          '#type' => 'textfield',
          '#title' => t('Confirm email'),
          '#default_value' => (!$registered ? $current_email : ''),
          '#required' => $element['#required'],
          '#attributes' => [
            'class' => ['email-confirm', 'js-email-confirm'],
            'placeholder' => t('Retype your email address'),
          ],
          '#field_suffix' => '<span class="domain-name">' . (!$registered ? $current_domain : '') . '</span>',
          '#error_no_message' => TRUE,
        );
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

    if ($account->isAnonymous()) {
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
    ];

    if (!empty($user)) {
      $user_email = $user->getEmail();

      if (!empty($user_email)) {
        $user_email = explode('@', $user->getEmail());
        $email_info = [
          'name' => $user_email[0],
          'domain' => '@' . $user_email[1],
        ];
      }
    }

    return $email_info;
  }

}
