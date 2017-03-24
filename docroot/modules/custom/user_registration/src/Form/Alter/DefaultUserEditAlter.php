<?php

namespace Drupal\user_registration\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;

/**
 * Class DefaultUserEditAlter.
 */
class DefaultUserEditAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * Checks that form is matched to specific conditions.
   *
   * @return bool
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * Form alter custom implementation.
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    $form['field_person_titles']['widget']['#options']['_none'] = t('Select title');

    $form['field_first_name']['widget'][0]['value']['#attributes']['placeholder'] = t('Please enter your first name');
    $form['field_middle_name']['widget'][0]['value']['#attributes']['placeholder'] = t('Please enter your middle name');
    $form['field_last_name']['widget'][0]['value']['#attributes']['placeholder'] = t('Please enter your last name');

    $form['account']['current_pass']['#weight'] = 10;
    $form['account']['pass']['#weight'] = 11;
    $form['account']['status']['#weight'] = 12;
    $form['account']['roles']['#weight'] = 13;
    $form['account']['current_pass']['#prefix'] = '<div class="form-item">';
    $form['account']['pass']['#suffix'] = '</div>';

    foreach ($form['field_organisation']['widget']['#options'] as $key => $option) {
      if (is_array($option)) {
        $form['field_organisation']['widget']['#options'] += $option;
        unset($form['field_organisation']['widget']['#options'][$key]);
      }
    }
    $states = array(
      'visible' => array(
        // @TODO Change this logic.
        'select[name="field_organisation"]' => array('value' => 485),
      ),
    );
    $form['field_non_member_organization']['#states'] = $states;

    unset(
      $form['account']['current_pass']['#description'],
      $form['account']['name'],
      $form['account']['mail']['#description'],
      $form['account']['name']['#description'],
      $form['account']['pass']['#description']
    );

    $form['actions']['submit']['#value'] = t('Submit changes');
    for ($i = 1; $i <= 3; $i++) {
      $form['submit' . $i] = $form['actions']['submit'];
    }
    $form['actions']['submit']['#access'] = FALSE;
  }

}
