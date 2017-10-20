<?php

namespace Drupal\events\Plugin\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\menu_item_visibility_by_group\MenuItemVisibilityCheckByGroup;
use Symfony\Component\Routing\Route;

/**
 * Class CalendarAccessCheck.
 */
class CalendarAccessCheck implements AccessInterface {

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * @var \Drupal\menu_item_visibility_by_group\MenuItemVisibilityCheckByGroup
   */
  protected $menuItemVisibilityCheckByGroup;

  /**
   * CalendarAccessCheck constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\menu_item_visibility_by_group\MenuItemVisibilityCheckByGroup $menuItemVisibilityCheckByGroup
   */
  public function __construct(AccountInterface $account, MenuItemVisibilityCheckByGroup $menuItemVisibilityCheckByGroup) {
    $this->account = $account;
    $this->menuItemVisibilityCheckByGroup = $menuItemVisibilityCheckByGroup;
  }

  /**
   * A custom access check.
   *
   * @param \Symfony\Component\Routing\Route $route
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @param \Drupal\group\Entity\GroupInterface $group
   *
   * @return \Drupal\Core\Access\AccessResult
   */
  public function access(Route $route, RouteMatchInterface $route_match, GroupInterface $group) {
    // List with groups that should have access to calendar page.
    $groups = array(
      'governance_area',
      'region',
      'country',
      'project',
      'region_protected',
      'country_protected',
      'project_protected',

    );
    $user_is_ga = $this->menuItemVisibilityCheckByGroup->check($this->account, $groups);
    $group_type = (in_array($group->getGroupType()->id(), $groups));
    return AccessResult::allowedIf($user_is_ga && $group_type);
  }

}
