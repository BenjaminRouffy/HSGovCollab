<?php

namespace Drupal\group_following;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;

class GroupFollowing implements GroupFollowingInterface {

  /**
   * @var GroupFollowingManagerInterface
   */
  protected $groupFollowingManager;
  /**
   * @var GroupInterface
   */
  protected $group;
  protected $accountFollowing;

  /**
   * GroupFollowing constructor.
   * @param GroupFollowingManagerInterface $group_following_manager
   * @param GroupInterface $group
   */
  function __construct(GroupFollowingManagerInterface $group_following_manager, GroupInterface $group) {
    $this->groupFollowingManager = $group_following_manager;
    $this->group = $group;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return GroupFollowingResult
   */
  public function getResultByAccount(AccountInterface $account) {
    if (!isset($this->accountFollowing[$account->id()])) {
      $this->accountFollowing[$account->id()] = new GroupFollowingResult($this, $account);
    }
    return $this->accountFollowing[$account->id()];
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return mixed
   */
  public function follow(AccountInterface $account) {

  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return mixed
   */
  public function unfollow(AccountInterface $account) {

  }

  /**
   * @param $account
   * @return int
   */
  public function getFollowerByGroupForUser(AccountInterface $account) {
    return $this->groupFollowingManager->getStorage()
      ->getFollowerByGroupForUser($this->group, $account);
  }

}
