<?php

namespace Drupal\group_customization\Access;

use Drupal\Core\Access\AccessResult;
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
   * Router access callback.
   *
   * Add an access verification to each route with group parameter.
   *
   * @see group_customization.services.yml
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {

    $result = parent::access($route, $route_match, $account);

    // Don't interfere if no group was specified.
    $parameters = $route_match->getParameters();
    if (!$parameters->has('group')) {
      return $result;
    }
    // Don't interfere if the group isn't a real group.
    $group = $parameters->get('group');
    $group_check = $this->checkAccess($group, 'view group', $account, [
      'published',
    ]);
    $result = $result->orIf($group_check);
    return $result;
  }

  /**
   * This function will check permission on entity load.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Group Entity.
   * @param string $operation
   *   String value most of cases contains ('view', 'view group', 'edit') etc.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Current account.
   * @param array $group_statuses
   *   Array of statuses that is allowed in current case.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result implementation.
   *
   * @see ContentEntityBase::access()
   * @see group_customization_group_access()
   */
  public function checkAccess(EntityInterface $entity, string $operation, AccountInterface $account, array $group_statuses = []) {
    $bypass = AccessResult::allowedIfHasPermissions($account, ['bypass group access']);
    $group_by_pass = GroupAccessResult::allowedIfHasGroupPermissions($entity, $account, [
      'bypass administer group status',
      'bypass administer group ' . $operation,
    ], 'OR');
    if (!$bypass->isAllowed() && !$group_by_pass->isAllowed()) {
      if ($entity instanceof GroupInterface) {
        if (!$entity->get('field_group_status')) {
          return AccessResult::neutral();
        }
        $group_status = $entity->get('field_group_status')->value ?: 'unpublished';
        $status = AccessResult::forbiddenIf(!in_array($group_status, $group_statuses));
        return $status;
      }
    }
    return AccessResult::neutral();
  }

}
