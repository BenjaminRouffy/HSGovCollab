<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\GroupMembershipLoader;

/**
 * Class GroupFollowingManager.
 *
 * @package Drupal\group_following
 */
class GroupFollowingManager implements GroupFollowingManagerInterface {

  /**
   * @var \Drupal\group\GroupMembershipLoader
   */
  protected $groupMembershipLoader;

  /**
   * @var \Drupal\group_following\GroupFollowingStorageInterface
   */
  protected $groupFollowingStorage;

  /**
   * Constructor.
   */
  public function __construct(GroupMembershipLoader $group_membership_loader, GroupFollowingStorageInterface $group_following_storage) {
    $this->groupMembershipLoader = $group_membership_loader;
    $this->groupFollowingStorage = $group_following_storage;
  }

  /**
   * Return a new GroupFollowingInterface class.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group object.
   *
   * @return GroupFollowingInterface
   *   GroupFollowingInterface class.
   */
  public function getFollowingByGroup(GroupInterface $group) {
    return new GroupFollowing($this, $group);
  }

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
  public function getFollowedForUser(AccountInterface $account, $group_type = NULL) {
    $gids = $this->getStorage()
      ->getFollowedForUser($account);
    $result = [];
    foreach ($gids as $gid) {
      $result[$gid->bundle][$gid->gid] = $gid->gid;
    }
    return $result;
  }

  /**
   * Storage getter.
   *
   * @return GroupFollowingStorageInterface
   *   Storage object.
   */
  public function getStorage() {
    return $this->groupFollowingStorage;
  }

  /**
   * {@inheritdoc}
   *
   * @deprecated
   *
   * @see GroupFollowing
   */
  public function addHardFollowing(GroupFollowing $group_following, AccountInterface $account) {
    // TODO: Implement addHardFollowing() method.
  }

}
