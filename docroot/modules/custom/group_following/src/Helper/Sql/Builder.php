<?php

namespace Drupal\group_following\Helper\Sql;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;

/**
 * Class Builder.
 */
class Builder {

  protected $connection;

  /**
   * Builder constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function subGroupParentLevel() {
    $select = db_select('groups', 'g');
    $select->addField('g', 'id', 'start_vertex');
    $select->addField('g', 'id', 'end_vertex');
    $select->addExpression('\'0\'', 'hops');
    $select->addExpression('\'0\'', 'exit_edge_id');

    $select->leftJoin('group_graph', 'gg', 'g.id = gg.end_vertex');
    $select->isNull('gg.hops');
    return $select;

  }

  /**
   * {@inheritdoc}
   */
  public function subGroupMaxLevel() {
    $select = db_select('group_graph', 'gg');
    $select->addField('gg', 'exit_edge_id', 'exit_edge_id');
    $select->addField('gg', 'end_vertex', 'end_vertex');
    $select->addExpression('MAX(gg.hops)', 'max');
    $select->groupBy('gg.exit_edge_id');
    $select->groupBy('gg.end_vertex');
    return $select;
  }

  /**
   * {@inheritdoc}
   */
  public function subGroupIncreaceHops() {
    $select = db_select($this->subGroupMaxLevel(), 'g');
    $select->addField('gg', 'start_vertex', 'start_vertex');
    $select->addField('gg', 'end_vertex', 'end_vertex');
    $select->addExpression('gg.hops + 1', 'hops');
    $select->addExpression('gg.exit_edge_id', 'exit_edge_id');

    $select->innerJoin('group_graph', 'gg', db_and()
      ->where('g.max = gg.hops')
      ->where('g.exit_edge_id = gg.exit_edge_id'));
    return $select;
  }

  /**
   * {@inheritdoc}
   */
  public function groupUnion() {
    $select = $this->subGroupParentLevel()
      ->union($this->subGroupIncreaceHops());
    return $select;
  }

  /**
   * {@inheritdoc}
   */
  public function groupFollowing($account) {
    $select = db_select('group_content_field_data', 'gcf');
    $select->addField('gcf', 'id', 'id');
    $select->addField('gcf', 'gid', 'gid');
    $select->addField('gcf', 'entity_id', 'uid');
    $select->addExpression('substring_index(gcff.bundle, \'-\', 1)', 'bundle');
    $select->addExpression('concat(\':\', IF(gcff.field_follower_value, if(substring_index(gcff.bundle, \'-\', 1) = \'region\', \'following:following\',
                                       \'following\'), if(substring_index(gcff.bundle, \'-\', 1) = \'region\', \'unfollowing:following\', \'unfollowing\')))', 'value');

    $select->innerJoin('group_content__field_follower', 'gcff', db_and()
      ->where('gcf.id = gcff.entity_id'));

    // @TODO Replace to $account.
    $select->condition('gcf.entity_id', \Drupal::currentUser()
      ->id()
    );
    return $select;
  }

}
