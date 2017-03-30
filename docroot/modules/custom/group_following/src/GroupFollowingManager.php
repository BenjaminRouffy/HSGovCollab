<?php

namespace Drupal\group_following;
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

}
