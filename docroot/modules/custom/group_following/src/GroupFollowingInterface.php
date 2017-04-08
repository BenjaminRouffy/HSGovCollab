<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;

interface GroupFollowingInterface {

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return GroupFollowingResult
   */
  public function getResultByAccount(AccountInterface $account);

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return mixed
   */
  public function follow(AccountInterface $account);

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return mixed
   */
  public function unfollow(AccountInterface $account);

}
