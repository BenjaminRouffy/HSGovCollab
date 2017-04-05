<?php

namespace Drupal\group_following\Plugin\views\join;

use Drupal\views\Annotation\ViewsJoin;
use Drupal\views\Plugin\views\join\JoinPluginBase;

/**
 * @ingroup views_join_handlers
 * @ViewsJoin("group_following")
 */
class GroupFollowing extends JoinPluginBase {

  /**
   * Helper service
   * @var \Drupal\group_following\Helper\Sql\Builder
   */
  protected $sqlBuilder;

  /**
   * Constructs a Subquery object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // @TODO I'm not sure that ContainerInjectionInterface is allowed here.
    $this->sqlBuilder = \Drupal::getContainer()
      ->get('group_following.builder_sql');

  }

  /**
   * Builds the SQL for the join this object represents.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $select_query
   *   The select query object.
   * @param string $table
   *   The base table to join.
   * @param \Drupal\views\Plugin\views\query\QueryPluginBase $view_query
   *   The source views query.
   */
  public function buildJoin($select_query, $table, $view_query) {
    $current_user = \Drupal::currentUser()->id();

    $group_membership_types = $this->sqlBuilder->getAllGroupMemberShipTypes();
    $group_roles_with_gid = $this->sqlBuilder->getGroupRolesWithGid($group_membership_types);

    $group_graph_with_own = $this->sqlBuilder->getGroupGraphWithOwn();


    $group_graph = db_select($group_graph_with_own, 'gg1');

    // @TODO DELETE
    $group_graph->addField('gg1', $this->field, 'gid');

    $iteration = 3;
    $group_graph->addExpression($this->sqlBuilder->getRoles($iteration), 'roles');
    $or = db_or();

    for ($i = 1; $i <= $iteration; $i++) {
      if ($i != 1) {
        $group_graph_with_own = $this->sqlBuilder->getGroupGraphWithOwn();
        $pi = $i - 1;
        $group_graph->leftJoin($group_graph_with_own, "gg{$i}", "gg{$pi}.end_vertex = gg{$i}.start_vertex AND gg{$i}.hops = :hops{$i}", [
          ':hops' . $i => $pi
        ]);
      }
      $group_graph->leftJoin($group_roles_with_gid, "grg{$i}", "gg{$i}.start_vertex = grg{$i}.gid AND grg{$i}.uid = :current_user", [
        ':current_user' => $current_user
      ]);

      $group_graph->addField("gg{$i}", "end_vertex", "end_vertex{$i}");
      $or->where("{$this->leftTable}.{$this->leftField} = {$table['alias']}.end_vertex{$i}");

    }
    $group_graph->condition('gg1.hops', 0);
    $group_graph->havingCondition('roles', '%:unfollower', 'NOT LIKE');
    $group_graph->havingCondition('roles', ':', '!=');

    $select_query->addJoin($this->type = 'INNER', $group_graph, $table['alias'], db_and()->condition($or));
    return;

  }

}
