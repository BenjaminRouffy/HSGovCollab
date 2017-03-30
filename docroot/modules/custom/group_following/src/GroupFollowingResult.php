<?php
/**
 * Created by PhpStorm.
 * User: spheresh
 * Date: 30/03/17
 * Time: 17:44
 */

namespace Drupal\group_following;


class GroupFollowingResult implements GroupFollowingResultInterface  {

  /**
   * Return TRUE in case isSoftFollower or isHardFollower is TRUE.
   *
   * @return bool
   */
  public function isFollower() {
    // TODO: Implement isFollower() method.
  }

  /**
   * Indicate that user has a parent followed entity.
   *
   * @return bool
   */
  public function isSoftFollower() {
    // TODO: Implement isSoftFollower() method.
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
