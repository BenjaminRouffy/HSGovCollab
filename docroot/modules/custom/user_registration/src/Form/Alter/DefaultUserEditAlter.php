<?php

namespace Drupal\user_registration\Form\Alter;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;

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
    $form['field_non_member_organization']['widget'][0]['value']['#attributes']['placeholder'] = t('Please enter your organisation');

    $form['account']['current_pass']['#weight'] = 10;
    $form['account']['pass']['#weight'] = 11;
    $form['account']['pass']['#type'] = 'boosted_password_confirm';
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

    $current_theme = \Drupal::service('theme.manager')->getActiveTheme();

    if ('ample' == $current_theme->getName()) {
      for ($i = 1; $i <= 3; $i++) {
        $form['submit' . $i] = $form['actions']['submit'];
      }
    }
    else {
      $form['submit3'] = $form['actions']['submit'];
    }
    $form['actions']['submit']['#access'] = FALSE;

    // Load "Profile useful information" block and attach it to the form.
    if ($block = BlockContent::load(45)) {
      $element = \Drupal::entityTypeManager()
        ->getViewBuilder('block_content')
        ->view($block);

      $form['info_block']['#type'] = 'container';
      $form['info_block']['#theme'] = 'useful_information';
      $form['info_block']['content'] = $element;
    }
  }

}
