<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\views\Plugin\views\join\JoinPluginBase;

/**
 * Interface GroupFollowingStorageInterface.
 *
 * @TODO Update interface.
 */
interface GroupFollowingStorageInterface {

  /**
   * {@inheritdoc}
   *
   * @file group_following/src/Plugin/views/join/GroupFollowing.php
   *   GroupFollowing::buildJoin.
   */
  function buildJoin(JoinPluginBase $join_plugin, $select_query, $table, $view_query);

  /**
   * {@inheritdoc}
   *
   * @return int
   *   Count of following references.
   */
  function getFollowerByGroupForUser(GroupInterface $group, AccountInterface $account);

}
