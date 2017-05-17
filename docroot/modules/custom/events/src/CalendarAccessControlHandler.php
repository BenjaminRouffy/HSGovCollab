<?php

namespace Drupal\events;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\menu_item_visibility_by_group\MenuItemVisibilityCheckByGroup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class CalendarAccessControlHandler implements ContainerInjectionInterface {

  /**
   * @var \Drupal\menu_item_visibility_by_group\MenuItemVisibilityCheckByGroup
   */
  protected $check_by_group;

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\menu_item_visibility_by_group\MenuItemVisibilityCheckByGroup $check_by_group */
    $check_by_group = $container->get('menu_item_visibility_by_group.check_by_group');
    return new static(
      $container,
      $check_by_group,
      \Drupal::currentUser()->getAccount()
    );
  }

  /**
   * CalendarAccessControlHandler constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param \Drupal\menu_item_visibility_by_group\MenuItemVisibilityCheckByGroup $check_by_group
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(ContainerInterface $container, MenuItemVisibilityCheckByGroup $check_by_group, AccountInterface $account) {
    $this->container = $container;
    $this->check_by_group = $check_by_group;
    $this->account = $account;
  }

  /**
   *
   */
  public function checkMembershipAccess() {
    if ($this->check_by_group->check($this->account, ['governance_area'])) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
