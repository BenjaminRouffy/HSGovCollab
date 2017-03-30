<?php

namespace Drupal\group_following;
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
   * @return GroupFollowing
   */
  public function getFollowingByGroup(GroupInterface $group) {
    // TODO: Implement getFollowingByGroup() method.
  }
}
