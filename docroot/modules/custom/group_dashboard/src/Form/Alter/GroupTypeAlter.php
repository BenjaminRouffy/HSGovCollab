<?php

namespace Drupal\group_dashboard\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\group\Entity\GroupType;

/**
 * Alter Group type form.
 */
class GroupTypeAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {
  use StringTranslationTrait;

  /**
   * @inheritdoc
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
    // Load the current node type configuration entity.
    $group_type = $form_state->getFormObject()->getEntity();

    $form['access_to_subgroup_functionality'] = [
      '#type' => 'checkbox',
      '#title' => t('Set access to subgroup functionality'),
      '#default_value' => $group_type->getThirdPartySetting('group_dashboard', 'access_to_subgroup_functionality', 0),
    ];

    // Process the submitted values before they are stored.
    $form['#entity_builders'][] = [$this, 'setFollowingSettings'];
  }

  /**
   * Save settings
   */
  function setFollowingSettings($entity_type, GroupType $group, &$form, FormStateInterface $form_state) {
    $group->setThirdPartySetting('group_dashboard', 'access_to_subgroup_functionality', $form_state->getValue('access_to_subgroup_functionality'));
  }

}
