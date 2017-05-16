<?php

namespace Drupal\menu_item_visibility_by_group;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\GroupMembershipLoader;

/**
 * Class MenuItemVisibilityCheckByGroup.
 *
 * @package Drupal\menu_item_visibility_by_group
 */
class MenuItemVisibilityCheckByGroup {

  /**
   * Drupal\group\GroupMembershipLoader definition.
   *
   * @var \Drupal\group\GroupMembershipLoader
   */
  protected $groupMembershipLoader;

  /**
   * Constructs a new MenuItemVisibilityCheckByGroup object.
   */
  public function __construct(GroupMembershipLoader $group_membership_loader) {
    $this->groupMembershipLoader = $group_membership_loader;
  }

  /**
   * Check the role access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param array $group_types_array
   *
   * @return bool
   */
  public function check(AccountInterface $account, $group_types_array = []) {
    if (!$account->hasPermission('all access to groups') && !empty($group_types_array)) {
      $user_memberships = $this->groupMembershipLoader->loadByUser();
      $user_group_types_memberships = [];
      /* @var GroupMembership $group_membership */
      foreach ($user_memberships as $group_membership) {
        if (!empty($group_membership->getGroup()->bundle())) {
          // Add the groups the user is a member of to use later on.
          $user_group_types_memberships[] = $group_membership->getGroup()->bundle();
        }
      }

      return !empty(array_intersect($group_types_array, $user_group_types_memberships));
    }

    // No group types were selected; visible to all.
    return TRUE;
  }

}
