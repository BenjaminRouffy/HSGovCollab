<?php

namespace Drupal\group_following;
use Drupal\group\Entity\GroupInterface;

/**
 * Interface GroupFollowingManagerInterface.
 *
 * @package Drupal\group_following
 */
interface GroupFollowingManagerInterface {

  /**
   * @param GroupInterface $group
   * @return GroupFollowing
   */
  public function getFollowingByGroup(GroupInterface $group);

}
