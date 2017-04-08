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
   * Drupal\group\GroupMembershipLoader definition.
   *
   * @var \Drupal\group\GroupMembershipLoader
   */
  protected $groupMembershipLoader;

  /**
   * @var \Drupal\group_following\GroupFollowingStorageInterface
   */
  protected $groupFollowingStorage;

  /**
   * Constructor.
   *
   * @param GroupMembershipLoader $group_membership_loader
   * @param GroupFollowingStorageInterface $group_following_storage
   */
  public function __construct(GroupMembershipLoader $group_membership_loader, GroupFollowingStorageInterface $group_following_storage) {
    $this->groupMembershipLoader = $group_membership_loader;
    $this->groupFollowingStorage = $group_following_storage;
  }

  /**
   * @param GroupInterface $group
   * @return GroupFollowingInterface
   */
  public function getFollowingByGroup(GroupInterface $group) {
    return new GroupFollowing($this, $group);
  }

  public function getFollowedForUser(AccountInterface $account, $group_type = NULL) {
    $gids = $this->getStorage()
      ->getFollowedForUser($account);
    if($group_type) {

      $query = \Drupal::entityQuery('group')
        ->condition('id', $gids, 'IN');

      $gids = $query->execute();
    }
    $result = [];
    foreach ($gids as $gid) {
      $group_following = new GroupFollowing($this, entity_load('group', $gid));
      $group_following->getResultByAccount($account)->setSoftFollower(TRUE);
      $result[] = $group_following;
    }
    return $result;
  }

  /**
   * @return GroupFollowingStorageInterface
   */
  public function getStorage() {
    return $this->groupFollowingStorage;
  }

  public function addHardFollowing(GroupFollowing $group_following, AccountInterface $account) {
    // TODO: Implement addHardFollowing() method.
  }

}
