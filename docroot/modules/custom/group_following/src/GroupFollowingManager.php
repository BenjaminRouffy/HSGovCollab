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
   * Constructor.
   */
  public function __construct(GroupMembershipLoader $group_membership_loader) {
    $this->groupMembershipLoader = $group_membership_loader;
  }

  /**
   * @param GroupInterface $group
   * @return GroupFollowingInterface
   */
  public function getFollowingByGroup(GroupInterface $group) {
    return new GroupFollowing();
  }

  /**
   * @return GroupFollowingStorageInteraface
   */
  public function getStorage() {
    // TODO: Implement getStorage() method.
  }

  public function addHardFollowing(GroupFollowing $group_following, AccountInterface $account) {
    // TODO: Implement addHardFollowing() method.
  }
}
