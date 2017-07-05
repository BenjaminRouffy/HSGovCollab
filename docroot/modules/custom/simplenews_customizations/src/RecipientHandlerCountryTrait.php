<?php

namespace Drupal\simplenews_customizations;

/**
 *
 */
trait RecipientHandlerCountryTrait {

  /**
   *
   */
  public function settingsForm($element, $settings, $form_state) {
    /** @var \Drupal\group\Entity\Group[] $groups */
    $groups = \Drupal::entityTypeManager()->getStorage('group')->loadMultiple();
    $options = [];
    foreach ($groups as $group) {
      $options[$group->getGroupType()->label()][$group->id()] = $group->label();
    }
    $element['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select group type'),
      '#options' => $options,
      '#empty_option' => $this->t('Select'),
      '#default_value' => ($settings['type'] ?: ''),
      '#ajax' => [
        'callback' => [$this, 'ajaxUpdateRecipientHandlerSettings'],
        'wrapper' => 'recipient-handler-count',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];
    return parent::settingsForm($element, $settings, $form_state);
  }

}
