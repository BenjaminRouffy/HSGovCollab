<?php

namespace Drupal\group_following;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group_following\Helper\Sql\Builder;
use Drupal\views\Plugin\views\join\JoinPluginBase;

class GroupFollowingStorage implements GroupFollowingStorageInterface {

  const ITERATION = 3;
  protected $connection;
  protected $sqlBuilder;

  function __construct(Connection $connection, Builder $sql_builder) {
    $this->connection = $connection;
    $this->sqlBuilder = $sql_builder;
  }

  public function generateJoin(AccountInterface $account) {
    $current_user = $account->id();

    $group_membership_types = $this->sqlBuilder->getAllGroupMemberShipTypes();
    $group_roles_with_gid = $this->sqlBuilder->getGroupRolesWithGid($group_membership_types);

    $group_graph_with_own = $this->sqlBuilder->getGroupGraphWithOwn();


    $group_graph = $this->connection->select($group_graph_with_own, 'gg1');

    $group_graph->addExpression($this->sqlBuilder->getRoles(static::ITERATION), 'roles');

    for ($i = 1; $i <= static::ITERATION; $i++) {
      if ($i != 1) {
        $group_graph_with_own = $this->sqlBuilder->getGroupGraphWithOwn();
        $pi = $i - 1;
        $group_graph->leftJoin($group_graph_with_own, "gg{$i}", "gg{$pi}.end_vertex = gg{$i}.start_vertex AND gg{$i}.hops = :hops{$i}", [
          ':hops' . $i => $pi
        ]);
      }
      $group_graph->leftJoin($group_roles_with_gid, "grg{$i}", "gg{$i}.end_vertex = grg{$i}.gid AND grg{$i}.uid = :current_user", [
        ':current_user' => $current_user
      ]);

      $group_graph->addField("gg{$i}", "end_vertex", "end_vertex{$i}");

    }
    $group_graph->condition('gg1.hops', 0);
    $group_graph->havingCondition('roles', '%:unfollower', 'NOT LIKE');
    $group_graph->havingCondition('roles', ':', '!=');

    return $group_graph;
  }

  public function buildJoin(JoinPluginBase $join_plugin, $select_query, $table, $view_query) {
    $current_user = \Drupal::currentUser()->getAccount();

    $group_graph = $this->generateJoin($current_user);

    $or = new Condition('OR');
    for ($i = 1; $i <= static::ITERATION; $i++) {
      $or->where("{$join_plugin->leftTable}.{$join_plugin->leftField} = {$table['alias']}.end_vertex{$i}");
    }

    $select_query->addJoin($join_plugin->type = 'INNER', $group_graph, $table['alias'], db_and()->condition($or));
  }


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
