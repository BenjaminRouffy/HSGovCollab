<?php

namespace Drupal\group_dashboard\Plugin\Condition;

use Drupal\user\Plugin\Condition\UserRole as BaseUserRole;

/**
 * Provides a 'User Role' condition.
 *
 * @Condition(
 *   id = "access_by_user_role",
 *   label = @Translation("User Role"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 */
class UserRole extends BaseUserRole {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['roles']) && !$this->isNegated()) {
      return TRUE;
    }

    $user = $this->getContextValue('user');

    // Check super admin.
    if ($user->id() == 1) {
      return TRUE;
    }

    return (bool) array_intersect($this->configuration['roles'], $user->getRoles());
  }

}
