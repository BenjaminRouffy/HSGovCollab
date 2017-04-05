<?php

namespace Drupal\group_following;

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
  protected $entity;

  /**
   * GroupFollowing constructor.
   * @param GroupFollowingManagerInterface $group_following_manager
   * @param GroupInterface $group
   */
  function __construct(GroupFollowingManagerInterface $group_following_manager, GroupInterface $group) {
    $this->groupFollowingManager = $group_following_manager;
    $this->entity = $group;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return GroupFollowingResult
   */
  public function getResultByAccount(AccountInterface $account) {
    return new GroupFollowingResult($this, $account);
  }

  public function follow() {
    $this->entity;
  }

  public function unfollow() {
    // TODO: Implement unfollow() method.
  }

  public function getFollowerByGroupForUser($entity) {
    return $this->groupFollowingManager->getStorage()->getFollowerByGroupForUser($this->entity, $entity);
  }
}
