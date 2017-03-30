<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;

class GroupFollowing implements GroupFollowingInterface {
  protected $entity;

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return GroupFollowingResult
   */
  public function getResultByAccount(AccountInterface $account) {
    return new GroupFollowingResult();
  }

  public function follow() {
    $this->entity;
  }

  public function unfollow() {
    // TODO: Implement unfollow() method.
  }
}
