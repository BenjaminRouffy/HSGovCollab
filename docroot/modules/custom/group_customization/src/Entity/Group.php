<?php

namespace Drupal\group_customization\Entity;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group as MainGroup;


/**
 * Defines the Group entity.
 *
 * @ingroup group
 *
 */
class Group extends MainGroup {

  /**
   * {@inheritdoc}
   */
  public function hasPermission($permission, AccountInterface $account) {
    // If the account can bypass all group access, return immediately.
    if ($account->hasPermission('bypass group access')) {
      return TRUE;
    }

    // Before anything else, check if the user can administer the group.
    if ($permission != 'administer group' && $this->hasPermission('administer group', $account)) {
      return TRUE;
    }

    // Retrieve all of the group roles the user may get for the group.
    $group_roles = $this->groupRoleStorage()->loadByUserAndGroup($account, $this);

    // Check each retrieved role for the requested permission.
    foreach ($group_roles as $group_role) {
      if ($group_role->hasPermission($permission)) {
        return TRUE;
      }
    }

    // If no role had the requested permission, we deny access.
    return FALSE;
  }
}
