<?php

namespace Drupal\group_following;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
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
   * {@inheritdoc}
   *
   * @file group_following/src/Plugin/views/join/GroupFollowing.php
   *   GroupFollowing::buildJoin.
   */
  public function buildJoin(JoinPluginBase $join_plugin, $select_query, $table, $view_query) {
    $select = $this->buildJoinQuery();

    $select_query->join($select, $table['alias'], db_and()
      ->where("{$join_plugin->leftTable}.{$join_plugin->leftField} = {$table['alias']}.{$join_plugin->field}")
//      ->condition("group_select.uid", \Drupal::currentUser()->getAccount()->id())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildJoinQuery() {
    $select_query = db_select($this->sqlBuilder->groupUnion(), 'group_union');
    $select_query->addField('group_union', 'end_vertex', 'gid');
    $select_query->addField('group_following', 'uid', 'uid');
    /** @var \Drupal\Core\Database\Query\Select $select_query */
    $alias = 'group_union';

    $select_query->leftJoin($this->sqlBuilder->groupFollowing(), 'group_following', db_and()
      ->where("{$alias}.start_vertex = group_following.gid"));

    $select_query->leftJoin('group_graph', 'group_graph_1', db_and()
      ->where("{$alias}.end_vertex = group_graph_1.end_vertex")
      ->where("{$alias}.exit_edge_id = group_graph_1.id"));


    $select_query->leftJoin($this->sqlBuilder->groupFollowing(), 'group_following_start', db_and()
      ->where("group_graph_1.start_vertex = group_following_start.gid")
      ->where("group_following.uid = group_following_start.uid"));
    $select_query->leftJoin($this->sqlBuilder->groupFollowing(), 'group_following_end', db_and()
      ->where("group_graph_1.end_vertex = group_following_end.gid")
      ->where("group_following_start.uid = group_following_end.uid"));

    $select_query->addExpression('CONCAT_WS(\',\', group_union.start_vertex,
            if(group_union.start_vertex != group_graph_1.start_vertex, group_graph_1.start_vertex, NULL),
            group_graph_1.end_vertex)', 'thread');
    $select_query->addExpression($expression = 'CONCAT_WS(\'\', IF(group_union.start_vertex = group_union.end_vertex, substring_index(group_following.value, \':\', 2), group_following.value),
            if(group_union.start_vertex != group_graph_1.start_vertex, group_following_start.value,
               NULL), group_following_end.value)', 'roles');

    $mysql_version = Database::getConnection()->version();
    list($main_version) = explode('-', $mysql_version);
    if (version_compare($main_version, '5.7') >= 0) {
      $select_query->having('length(roles) > 0');
      $select_query->havingCondition('roles', '%:following', 'LIKE');
    }
    else {
      $select_query->where("length($expression) > 0");
      $select_query->where($expression . ' LIKE \'%:following\'');
    }
    return $select_query;
  }

  /**
   * {@inheritdoc}
   *
   * @return int
   *   Count of following references.
   */
  public function getFollowerByGroupForUser(GroupInterface $group, AccountInterface $account) {
    $db_name = $this->buildJoinQuery();
    $select = db_select($db_name, 'group_select');
    $select->fields('group_select');
    $condition = db_and()->condition("group_select.gid", $group->id())
      ->condition("group_select.uid", $account->id());
    $select->condition($condition);
    $result = $select->countQuery();
    return $result->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function getFollowedForUser(AccountInterface $account) {
    $db_name = $this->buildJoinQuery();
    $select = db_select($db_name, 'group_select');
    $select->fields('group_select');
    $select->condition("group_select.uid", $account->id());
    $result = $select->execute()->fetchCol();
    return $result;
  }

}
