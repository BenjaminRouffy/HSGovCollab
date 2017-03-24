<?php

namespace Drupal\group_customization\Access;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessCheck;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\GroupPermissionAccessCheck as DefaultGroupPermissionAccessCheck;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\Routing\Route;

class GroupPermissionAccessCheck extends EntityAccessCheck  implements AccessInterface  {
  function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {

    // @TODO Ensure that we can use this permission here.
    $bypass = AccessResult::allowedIfHasPermissions($account, ['bypass group access']);
    // The next step is required just for non-permitted users.
    if(!$bypass->isAllowed()) {
      // EntityAccessCheck result.
      $result = parent::access($route, $route_match, $account);

      // Don't interfere if no group was specified.
      $parameters = $route_match->getParameters();
      if (!$parameters->has('group')) {
        return $result;
      }
      // Don't interfere if the group isn't a real group.
      $group = $parameters->get('group');

      if ($group instanceof \Drupal\group\Entity\GroupInterface) {
        if(!$group->get('field_group_status')) {
          return $result;
        }
        $group_status = $group->get('field_group_status')->value;

        $status = AccessResult::neutral();
        $status = $status->orIf($result);
        $status = $status->orIf(AccessResult::forbiddenIf(in_array($group_status, [
          'unpublished',
          'content',
        ])));
        return $status;
      }

      return $result;
    }
    return AccessResult::neutral();
  }
}
