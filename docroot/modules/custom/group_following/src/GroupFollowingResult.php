<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;

/**
 * Class GroupFollowingResult.
 */
class GroupFollowingResult implements GroupFollowingResultInterface {

  /**
   * @var GroupFollowing
   */
  protected $groupFollowing;

  /**
   * @var AccountInterface
   */
  protected $entity;

  /**
   * GroupFollowingResult constructor.
   *
   * @param GroupFollowing $group_following
   * @param AccountInterface $account
   */
  function __construct(GroupFollowing $group_following, AccountInterface $account) {
    $this->groupFollowing = $group_following;
    $this->entity = $account;
  }

  /**
   * Return TRUE in case isSoftFollower or isHardFollower is TRUE.
   *
   * @return bool
   */
  public function isFollower() {
    return $this->isHardFollower() || $this->isSoftFollower();
  }

  /**
   * Indicate that user has a parent followed entity.
   *
   * @return bool
   */
  public function isSoftFollower() {
    return $this->groupFollowing->getFollowerByGroupForUser($this->entity);
  }

  /**
   * Indicate that user has a hard membership.
   *
   * @return bool
   */
  public function isHardFollower() {
    // TODO: Implement isHardFollower() method.
  }

}
