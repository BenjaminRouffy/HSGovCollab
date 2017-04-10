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
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $group_type = $this->groupType->loadMultiple();
    $options = [];

    foreach ($group_type as $entity_type) {
      $options[$entity_type->id()] = $entity_type->label();
    }

    $form['gid'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Group type'),
      '#options' => $options,
      '#default_value' => $this->options['gid'],
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $group_types = $this->groupType->loadMultiple($this->options['gid']);
    $group_types_id = [];
    foreach ($group_types as $type) {
      $group_types_id[] = $type->id();
    }
    $default_value = (array) $this->value;
    $options = [];

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
    $query->condition('type', $group_types_id, 'IN');
    $groups = $this->group->loadMultiple($query->execute());

    $options = $this->getUserGroupMembership($group_types, $groups);

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

  /**
   * Check user's membership in Group.
   *
   * @param array $group_types
   *   Array of group types.
   * @param array $groups
   *   Array of groups.
   *
   * @return array
   *   Return array of user's groups.
   */
  protected function getUserGroupMembership($group_types, $groups) {
    $result = [];
    $options = [];
    $roles = [];

    $user = \Drupal::currentUser();
    // @TODO We should try to change it to $group->access().

    /** @var \Drupal\group\GroupMembershipLoaderInterface $membership_loader */
    $membership_loader = \Drupal::service('group.membership_loader');

    if (!$user->hasPermission('all access to groups')) {
      // Get group admin roles.
      foreach ($group_types as $group_type) {
        // @TODO We should to set admin roles to one name.
        if ('governance_area' == $group_type->id()) {
          $roles[] = $group_type->id() . '-manager';
        }
        else {
          $roles[] = $group_type->id() . '-admin';
        }
      }
      // Get all user memberships by admin roles.
      // @todo GroupMembershipSubscriber::onAlterMembershipsByUser() should be changed to current P4H business logic.
      $all_user_memberships = $membership_loader->loadByUser($user, $roles);

      if (!empty($all_user_memberships)) {
        foreach ($all_user_memberships as $group) {
          // Only group admins can moderate group content.          
          //if ($group->hasPermission('edit group')) {
            $result = array_merge($result, [
              $group->getGroupContent()->get('gid')->target_id,
            ]);
          //}
        }
      }
    }


    foreach ($groups as $group) {
      // @TODO We should try to change it to $group->access().
      // @see \Drupal\p4h_views_plugins\Plugin\views\filter\GroupIndexByGroupType
      // @line 107
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

    return $options;
  }
}
