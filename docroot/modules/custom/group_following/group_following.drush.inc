<?php
/**
 * @file
 */

/**
 * Implements hook_drush_command().
 */
function group_following_drush_command() {
  $items = [];
  $items['following-cache-rebuild'] = [];
  return $items;
}

/**
 * Call back function.
 */
function drush_group_following_following_cache_rebuild() {

  db_truncate('group_following_cache')
    ->execute();

  /** @var \Drupal\group_following\GroupFollowingStorage $group_following */
  $group_following = \Drupal::service('group_following.storage');
  $select = $group_following->buildJoinQuery(FALSE);

  db_insert('group_following_cache')
    ->fields([
      'gid',
      'uid',
      'thread',
      'roles',
    ])
    ->from($select)
    ->execute();

}
