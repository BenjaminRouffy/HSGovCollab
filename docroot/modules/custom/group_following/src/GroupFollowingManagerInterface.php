<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;

/**
 * Interface GroupFollowingManagerInterface.
 *
 * @package Drupal\group_following
 */
interface GroupFollowingManagerInterface {

  /**
   * Return a new GroupFollowingInterface class.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group object.
   *
   * @return GroupFollowingInterface
   *   GroupFollowingInterface class.
   */
  public function getFollowingByGroup(GroupInterface $group);

  /**
   * Array all following of user grouped by groups.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User object.
   * @param null $group_type
   *   Add implementation here.
   *
   * @return array
   *   Array all following of user grouped by groups.
   */
  public function getFollowedForUser(AccountInterface $account, $group_type = NULL);

  /**
   * Storage getter.
   *
   * @return GroupFollowingStorageInterface
   *   Storage object.
   */
  public function getStorage();

}
