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
   * @return mixed
   */
  public function follow();

  /**
   * @return mixed
   */
  public function unfollow();

}
