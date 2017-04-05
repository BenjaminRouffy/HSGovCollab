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
   * @param GroupInterface $group
   * @return GroupFollowingInterface
   */
  public function getFollowingByGroup(GroupInterface $group);

  /**
   * @return GroupFollowingStorageInterface
   */
  public function getStorage();

  public function addHardFollowing(GroupFollowing $group_following, AccountInterface $account);

}
