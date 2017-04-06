<?php

namespace Drupal\group_following;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupoupInterface;
use Drupal\group_following\Helper\Sql\Builder;
use Drupal\views\Plugin\views\join\JoinPluginBase;

/**
 * Class GroupFollowingStorage.
 */
class GroupFollowingStorage implements GroupFollowingStorageInterface {

  const ITERATION = 3;
  protected $connection;
  protected $sqlBuilder;

  /**
   * GroupFollowingStorage constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\group_following\Helper\Sql\Builder $sql_builder
   *   Group following helper service.
   */
  public function __construct(Connection $connection, Builder $sql_builder) {
    $this->connection = $connection;
    $this->sqlBuilder = $sql_builder;
  }

  /**
   * Generate skeleton.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   Nested query.
   */
  protected function generateJoin(AccountInterface $account) {
    $current_user = $account->id();

    // All membership group types "region-group_membership" etc...
    $group_membership_types = $this->sqlBuilder->getAllGroupMemberShipTypes();

    $group_roles_with_gid = $this->sqlBuilder->getGroupRolesWithGid($group_membership_types);
    $group_graph_with_own = $this->sqlBuilder->getGroupGraphWithOwn();

    $group_graph = $this->connection->select($group_graph_with_own, 'gg1');

    $expression = $this->sqlBuilder->getRoles(static::ITERATION);
    $group_graph->addExpression($expression, 'roles');

    for ($level_depth = 1; $level_depth <= static::ITERATION; $level_depth++) {
      if ($level_depth != 1) {
        // We skips the first level, because mail table contains
        // all information that we need.
        $group_graph_with_own = $this->sqlBuilder->getGroupGraphWithOwn();
        $parent_level_depth = $level_depth - 1;
        $group_graph->leftJoin($group_graph_with_own, "gg{$level_depth}", "gg{$parent_level_depth}.end_vertex = gg{$level_depth}.start_vertex AND gg{$level_depth}.hops = :hops{$level_depth}", [
          ':hops' . $level_depth => $parent_level_depth,
        ]);
      }
      $group_graph->leftJoin($group_roles_with_gid, "grg{$level_depth}", "gg{$level_depth}.end_vertex = grg{$level_depth}.gid AND grg{$level_depth}.uid = :current_user", [
        ':current_user' => $current_user,
      ]);

      $group_graph->addField("gg{$level_depth}", "end_vertex", "end_vertex{$level_depth}");

    }
    $group_graph->condition('gg1.hops', 0);

    /*
     * This code designed as for compatibility with mysql 5.5.
     *
     * @best_prastise
     *  $group_graph->havingCondition('role', '%:unfollower', 'NOT LIKE')
     */
    $group_graph->where($expression . 'NOT LIKE \'%:unfollower\'');
    $group_graph->where($expression . ' != \':\'');

    return $group_graph;
  }

  /**
   * {@inheritdoc}
   *
   * @file group_following/src/Plugin/views/join/GroupFollowing.php
   *   GroupFollowing::buildJoin.
   */
  public function buildJoin(JoinPluginBase $join_plugin, $select_query, $table, $view_query) {
    $current_user = \Drupal::currentUser()->getAccount();

    $group_graph = $this->generateJoin($current_user);

    $or = new Condition('OR');
    for ($i = 1; $i <= static::ITERATION; $i++) {
      $or->where("{$join_plugin->leftTable}.{$join_plugin->leftField} = {$table['alias']}.end_vertex{$i}");
    }

    $select_query->addJoin($join_plugin->type = 'INNER', $group_graph, $table['alias'], db_and()->condition($or));
  }

  /**
   * {@inheritdoc}
   *
   * @return int
   *   Count of following references.
   */
  public function getFollowerByGroupForUser(GroupInterface $group, AccountInterface $account) {
    $group_graph = $this->generateJoin($account);

    $iteration = 3;
    $or = new Condition('OR');

    for ($i = 1; $i <= $iteration; $i++) {
      $or->condition("gg{$i}.end_vertex", $group->id());
    }

    $result = $group_graph->condition($or)->countQuery();
    return $result->execute()->fetchField();
  }

}
