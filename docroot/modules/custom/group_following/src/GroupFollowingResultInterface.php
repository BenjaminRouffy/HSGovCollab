<?php

namespace Drupal\group_following;

interface GroupFollowingResultInterface {

  /**
   * Return TRUE in case isSoftFollower or isHardFollower is TRUE.
   *
   * @return bool
   */
  public function isFollower();

  /**
   * Indicate that user has a parent followed entity.
   *
   * @return bool
   */
  public function isSoftFollower();

  /**
   * Indicate that user has a hard membership.
   *
   * @return bool
   */
  public function isHardFollower();

}
