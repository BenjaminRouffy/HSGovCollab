<?php

namespace Drupal\group_dashboard\Plugin\views\filter;

use Drupal\p4h_views_plugins\Plugin\views\filter\GroupIndexByGroupType;
use Drupal\Core\Form\FormStateInterface;

/**
 * Filter by Parent Group id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_select_gid")
 */
class ParentGroupSelect extends GroupIndexByGroupType  {
  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $group_type = $this->groupType->load($this->options['gid']);
    $options = [];
    $result = [];

    $user = \Drupal::currentUser();

    $default_value = (array) $this->value;

    if ($exposed = $form_state->get('exposed')) {
      $identifier = $this->options['expose']['identifier'];

      if (!empty($this->options['expose']['reduce'])) {
        $options = $this->reduceValueOptions($options);

        if (!empty($this->options['expose']['multiple']) && empty($this->options['expose']['required'])) {
          $default_value = [];
        }
      }

      if (empty($this->options['expose']['multiple'])) {
        if (
          empty($this->options['expose']['required']) &&
          (empty($default_value) || !empty($this->options['expose']['reduce']))
        ) {
          $default_value = 'All';
        }
        elseif (empty($default_value)) {
          $keys = array_keys($options);
          $default_value = array_shift($keys);
        }
        // Due to #1464174 there is a chance that array('') was saved in the admin ui.
        // Let's choose a safe default value.
        elseif ($default_value == ['']) {
          $default_value = 'All';
        }
        else {
          $copy = $default_value;
          $default_value = array_shift($copy);
        }
      }
    }

    $query = \Drupal::entityQuery('group')
      // @todo Sorting on vocabulary properties -
      //   https://www.drupal.org/node/1821274.
      ->addTag('group_access');
    $query->condition('type', $group_type->id());

    if (!$user->hasPermission('all access to groups')) {
      $parent_group = $group_type->id();
      $parent_query = \Drupal::database()->select('group_content_field_data', 'group_content_field_data')
        ->fields('group_content_field_data', ['gid'])
        ->condition('group_content_field_data.type', "$parent_group-group_membership", 'LIKE')
        ->condition('group_content_field_data.entity_id', $user->id());

      $parent_query->leftJoin('group_content__group_roles', 'group_content__group_roles', 'group_content__group_roles.entity_id=group_content_field_data.id');
      $result = $parent_query->condition('group_content__group_roles.group_roles_target_id', '%-admin', 'LIKE')
        ->execute()
        ->fetchCol();

      // Not show any group is user not has admin access.
      $result = empty($result) ? [0] : $result;
    }

    $groups = $this->group->loadMultiple($query->execute());

    foreach ($groups as $group) {
      if (!$user->hasPermission('all access to groups')) {
        if (array_search($group->id(), $result) !== FALSE) {
          $options[$group->id()] = \Drupal::entityManager()
            ->getTranslationFromContext($group)
            ->label();
        }
      }
      else {
        $options[$group->id()] = \Drupal::entityManager()
          ->getTranslationFromContext($group)
          ->label();
      }
    }

    asort($options);
    $form_state->set('filter_options', $options);

    $form['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Select group'),
      '#multiple' => TRUE,
      '#options' => $options,
      '#size' => min(9, count($options)),
      '#default_value' => $default_value,
      '#access' => !empty($options),
    ];

    $user_input = $form_state->getUserInput();

    if ($exposed && isset($identifier) && !isset($user_input[$identifier])) {
      $user_input[$identifier] = $default_value;
      $form_state->setUserInput($user_input);
    }

  }
}
