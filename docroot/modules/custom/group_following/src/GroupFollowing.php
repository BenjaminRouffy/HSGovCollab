<?php

namespace Drupal\group_following;


use Drupal\Core\Session\AccountInterface;

class GroupFollowing implements GroupFollowingInterface {

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return GroupFollowingResult
   */
  public function isFollower(AccountInterface $account) {
    return new GroupFollowingResult();
  }

}
