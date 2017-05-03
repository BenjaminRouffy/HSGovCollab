<?php

namespace Drupal\group_dashboard\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\group\Entity\GroupType;
use Drupal\group\Entity\GroupTypeInterface;

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
    /* @var GroupTypeInterface $group_type */
    $group_type = $form_state->getFormObject()->getEntity();
    $options = [];

    $form['access_to_related_entities_functionality'] = [
      '#type' => 'checkbox',
      '#title' => t('Set access to related entities functionality'),
      '#default_value' => $group_type->getThirdPartySetting('group_dashboard', 'access_to_related_entities_functionality', 0),
    ];

    foreach ($group_type->getInstalledContentPlugins() as $plugin) {
      $options[$plugin->getContentTypeConfigId()] = $this->t('Access to related: @plugin', [
        '@plugin' => $plugin->getContentTypeLabel(),
      ]);
    }

    $form['access_to_different_entities'] = [
      '#type' => 'checkboxes',
      '#title' => t('Set access to relate different entities'),
      '#options' => $options,
      '#default_value' => $group_type->getThirdPartySetting('group_dashboard', 'access_to_different_entities', []),
      '#states' => [
        'visible' => [
          ':input[name="access_to_related_entities_functionality"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['access_to_subgroup_functionality'] = [
      '#type' => 'checkbox',
      '#title' => t('Set access to subgroup functionality'),
      '#default_value' => $group_type->getThirdPartySetting('group_dashboard', 'access_to_subgroup_functionality', 0),
    ];

    $form['hide_follow_button'] = [
      '#type' => 'checkbox',
      '#title' => t('Hide follow button on group'),
      '#default_value' => $group_type->getThirdPartySetting('group_dashboard', 'hide_follow_button', 0),
    ];

    // Process the submitted values before they are stored.
    $form['#entity_builders'][] = [$this, 'setFollowingSettings'];
  }

  /**
   * Save settings
   */
  function setFollowingSettings($entity_type, GroupType $group, &$form, FormStateInterface $form_state) {
    $group->setThirdPartySetting('group_dashboard', 'access_to_subgroup_functionality', $form_state->getValue('access_to_subgroup_functionality'));
    $group->setThirdPartySetting('group_dashboard', 'access_to_related_entities_functionality', $form_state->getValue('access_to_related_entities_functionality'));
    $group->setThirdPartySetting('group_dashboard', 'access_to_different_entities', $form_state->getValue('access_to_different_entities'));
    $group->setThirdPartySetting('group_dashboard', 'hide_follow_button', $form_state->getValue('hide_follow_button'));
  }

}
