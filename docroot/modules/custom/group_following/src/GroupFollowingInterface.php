<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;

interface GroupFollowingInterface {

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return GroupFollowingResult
   */
  public function getResultByAccount(AccountInterface $account);

  public function follow();

  public function unfollow();


}
