<?php

namespace Drupal\user_registration\Form\Alter;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
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

    if (!empty($form['account']['current_pass']) && $form['account']['current_pass']['#access'] !== FALSE) {
      $form['account']['current_pass']['#prefix'] = '<div class="form-item">';
      $form['account']['pass']['#suffix'] = '</div>';
    }

    foreach ($form['field_organisation']['widget']['#options'] as $key => $option) {
      if (is_array($option)) {
        $form['field_organisation']['widget']['#options'] += $option;
        unset($form['field_organisation']['widget']['#options'][$key]);
      }
    }

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

    /** @var \Drupal\user_registration\CollapsibleBlockService $collapsible_block */
    $collapsible_block = \Drupal::service('user_registration.collapsible_block');

    // Load "Profile useful information" block and attach it to the form.
    // @see \Drupal\user_registration\Controller\UserController::toggleUsefulInfo
    if ($block = BlockContent::load(45)) {
      $element = \Drupal::entityTypeManager()
        ->getViewBuilder('block_content')
        ->view($block);

      $form['info_block']['#type'] = 'container';
      $form['info_block']['#theme'] = 'useful_information';
      $form['info_block']['#collapsed'] = $collapsible_block->isCollapsed($block);
      $form['info_block']['#toggle'] = Url::fromRoute('user_registration.toggle_useful_info', ['user' => \Drupal::currentUser()->id()]);
      $form['info_block']['content'] = $element;
    }

    // Add 'Good to know' block to the form.
    // @see Drupal\user_registration\ProfileOnetimeForm
    if($block = BlockContent::load(22)) {
      $form['good_to_know_block'] = \Drupal::entityManager()
        ->getViewBuilder('block_content')
        ->view($block);

      $form['good_to_know_block']['field_title']['#theme_wrappers'][] = 'good_to_know';
      $form['good_to_know_block']['#prefix'] = '<div class="content top-text-region">';
      $form['good_to_know_block']['#suffix'] = '</div>';
      $form['good_to_know_block']['#weight'] = 1;
    }
  }

}
