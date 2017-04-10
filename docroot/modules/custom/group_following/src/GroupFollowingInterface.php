<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;

interface GroupFollowingInterface {

  /**
   * Gets a group following result by user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   *
   * @return GroupFollowingResult
   *   Group result object.
   */
  public function getResultByAccount(AccountInterface $account);

  /**
   * Create/update a membership with a field_follower field.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   */
  public function follow(AccountInterface $account);

  /**
   * Change a field_follower value to FALSE.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   */
  public function unfollow(AccountInterface $account);

  /**
   * Counts a user's following based on group.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account.
   *
   * @return int
   *   Count following based on group.
   */
  public function getFollowerByGroupForUser(AccountInterface $account);

}
