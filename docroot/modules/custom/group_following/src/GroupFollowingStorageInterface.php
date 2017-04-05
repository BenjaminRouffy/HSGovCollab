<?php

namespace Drupal\group_following;


use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\views\Plugin\views\join\JoinPluginBase;

interface GroupFollowingStorageInterface {

  public function generateJoin(AccountInterface $account);

  public function buildJoin(JoinPluginBase $join_plugin, $select_query, $table, $view_query);

  public function getFollowerByGroupForUser(GroupInterface $group, AccountInterface $account);


}
