<?php

namespace Drupal\group_following;

/**
 * Interface GroupFollowingManagerInterface.
 *
 * @package Drupal\group_following
 */
interface GroupFollowingManagerInterface {

  /**
   * @param \Drupal\group_following\GroupInterface $group
   * @return GroupFollowing
   */
  public function getFollowingByGroup(GroupInterface $group);

}
