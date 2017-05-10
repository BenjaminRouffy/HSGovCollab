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
  public function buildJoin(JoinPluginBase $join_plugin, $select_query, $table, $view_query, $type = 'INNER') {
    $select = $this->buildJoinQuery();

    $select_query->addJoin($type, $select, $table['alias'], db_and()
      ->where("{$join_plugin->leftTable}.{$join_plugin->leftField} = {$table['alias']}.{$join_plugin->field}")
//      ->condition("group_select.uid", \Drupal::currentUser()->getAccount()->id())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildJoinQuery($cache = TRUE) {
    if($cache && db_table_exists('group_following_cache')) {
      return 'group_following_cache';
    }
    // The group_union table has a same structure with group_graph,
    // but additionally has zero level of group's dependency.
    // @TODO Get rid of this, can be moved to group entity update hook.
    $select_query = db_select($this->sqlBuilder->groupUnion(), 'group_union');

    $select_query->addField('group_union', 'end_vertex', 'gid');
    $select_query->addField('u', 'uid', 'uid');

    /** @var \Drupal\Core\Database\Query\Select $select_query */
    $alias = 'group_union';

    $select_query->leftJoin('group_graph', 'group_graph_1', db_and()
      ->where("{$alias}.end_vertex = group_graph_1.end_vertex")
      ->where("{$alias}.exit_edge_id = group_graph_1.id"));

    // Understanding, that thread is nothing without users.
    $select_query->addJoin('CROSS', 'users', 'u', NULL, []);

    // Here I just struggle with the structure of the group_graph
    // Finally waiting of level1,level2,level3 thread line.
    $select_query->addExpression('CONCAT_WS(\',\', ' .
      'group_union.start_vertex, ' .
      ($second_condition = 'if(' .
        'group_union.start_vertex != group_graph_1.start_vertex, ' .
        'group_graph_1.start_vertex, ' .
        'group_graph_1.end_vertex' .
        ')') . ', ' .
      ($third_condition = 'if(' .
        'group_union.start_vertex = group_graph_1.start_vertex, ' .
        'NULL, ' .
        'group_graph_1.end_vertex' .
        ')') .
    ')', 'thread');


    // @TODO Get rid of this, groupFollowing will return nested query,
    // but the follower table will not be updated as often really ...
    $select_query->leftJoin($this->sqlBuilder->groupFollowing(), 'group_following_level_first', db_and()
      ->where("{$alias}.start_vertex = group_following_level_first.gid")
      // Yes, every time we will get the value for each follower and his group...
      // too hard probably ... but such a logic
      ->where("u.uid = group_following_level_first.uid"));
    // @TODO This logic will look better when we will store this matrix in a temporary table
    $select_query->leftJoin($this->sqlBuilder->groupFollowing(), 'group_following_level_second', db_and()
      ->where("{$second_condition} = group_following_level_second.gid")
      ->where("u.uid = group_following_level_second.uid"));
    $select_query->leftJoin($this->sqlBuilder->groupFollowing(), 'group_following_level_third', db_and()
      ->where("{$third_condition} = group_following_level_third.gid")
      ->where("u.uid = group_following_level_third.uid"));

    $select_query->addExpression($expression = 'CONCAT_WS(\'\', ' .
      'IF(' .
      // Auto following all countries by region is hidden here
      // ":unfollowing:following" will be replaced
      // to ":unfollowing" for zero level.
      // @TODO Maybe will better to check a group type.
      // IT can be taken form group_following_level_[level] tables.
      'group_union.start_vertex = group_union.end_vertex, ' .
      'substring_index(group_following_level_first.value, \':\', 2), ' .
      'group_following_level_first.value' .
      '), ' .
      'group_following_level_second.value, ' .
      'group_following_level_third.value' .
    ')', 'roles');

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
    $select_query->condition(db_or()
      // Ignoring empty rows.
      ->isNotNull("group_following_level_first.uid")
      ->isNotNull("group_following_level_second.uid")
      ->isNotNull("group_following_level_third.uid"));
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
  public function getFollowedForUser(AccountInterface $account, $cache = TRUE) {
    $select = $this->getFollowedForUserSql($account, $cache);
    $result = $select->execute()->fetchCol();
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getFollowedForUserSql(AccountInterface $account, $cache = TRUE) {
    $db_name = $this->buildJoinQuery($cache);
    $select = db_select($db_name, 'group_select');
    $select->fields('group_select');
    $select->condition("group_select.uid", $account->id());
    return $select;
  }

}
