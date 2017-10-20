<?php

namespace Drupal\group_customization\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityAccessCheck;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\GroupAccessResult;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\Routing\Route;

/**
 * Class GroupPermissionAccessCheck.
 */
class GroupPermissionAccessCheck extends EntityAccessCheck implements AccessInterface {

  /**
   * This function will check permission for views exposed filter.
   *
   * @param EntityInterface $entity
   *   Group Entity.
   * @param AccountInterface $account
   *   Current account.
   * @param array $group_statuses
   *   Array of statuses that is allowed in current case.
   *
   * @return AccessResultInterface
   *   Access result implementation.
   */
  public function checkAccessForFilter(EntityInterface $entity, AccountInterface $account, array $group_statuses = []) {
    if ($entity instanceof GroupInterface) {
      if ($entity->hasField('field_group_status')) {
        if (!$entity->get('field_group_status')) {
          return AccessResult::neutral();
        }

        $group_status = $entity->get('field_group_status')->value ?: 'unpublished';
        $status = AccessResult::allowedIf(in_array($group_status, $group_statuses));

        return $status;
      }
      else {
        $bypass = AccessResult::allowedIfHasPermissions($account, ['bypass group access']);

        if (GroupAccessResult::allowedIfHasGroupPermission($entity, $account, 'view group')->isAllowed() || $bypass->isAllowed()) {
          return AccessResult::allowed();
        }
        else {
          return AccessResult::forbidden();
        }
      }
    }

    return GroupAccessResult::allowedIfHasGroupPermission($entity, $account, 'access to view all group content');
  }

}
