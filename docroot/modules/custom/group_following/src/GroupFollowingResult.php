<?php

namespace Drupal\group_following;

use Drupal\Core\Database\Query\SelectInterface;
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
  protected $account;

  /**
   * @var bool
   */
  protected $softFollower;

  /**
   * GroupFollowingResult constructor.
   *
   * @param GroupFollowing $group_following
   * @param AccountInterface $account
   */
  function __construct(GroupFollowing $group_following, AccountInterface $account) {
    $this->groupFollowing = $group_following;
    $this->account = $account;
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
    return $this->getSoftFollower();
  }

  /**
   * Indicate that user has a hard membership.
   *
   * @return bool
   */
  public function isHardFollower() {
    // TODO: Implement isHardFollower() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getSoftFollower() {
    if (is_null($this->softFollower)) {
      $this->setSoftFollower($this->groupFollowing->getFollowerByGroupForUser($this->account));
    }
    return $this->softFollower;
  }

  /**
   * {@inheritdoc}
   */
  public function setSoftFollower($softFollower) {
    $this->softFollower = (bool) $softFollower;
  }

  /**
   * {@inheritdoc}
   */
  public function follow() {
    return $this->groupFollowing->follow($this->account);
  }

  /**
   * {@inheritdoc}
   */
  public function unfollow() {
    return $this->groupFollowing->unfollow($this->account);
  }

}
