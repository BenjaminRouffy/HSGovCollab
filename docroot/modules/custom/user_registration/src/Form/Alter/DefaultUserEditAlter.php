<?php

namespace Drupal\user_registration\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;

/**
 * Class RegisterUserLoginAlter.
 */
class DefaultUserEditAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface  {

  /**
   * Checks that form is matched to specific conditions.
   *
   * @return boolean
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * Form alter custom implementation.
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    $form['field_person_titles']['widget']['#options']['_none'] = t('Select title');

    $form['field_first_name']['widget'][0]['value']['#attributes']['placeholder'] = t('Please enter your name');
    $form['field_middle_name']['widget'][0]['value']['#attributes']['placeholder'] = t('Please enter your middle name');
    $form['field_last_name']['widget'][0]['value']['#attributes']['placeholder'] = t('Please enter your last name');

    unset(
      $form['account']['current_pass']['#description'],
      $form['account']['mail']['#description'],
      $form['account']['name']['#description'],
      $form['account']['pass']['#description']
    );

  }
}
