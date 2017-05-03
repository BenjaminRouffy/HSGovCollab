<?php

namespace Drupal\knowledge_vault\Plugin\views\argument;

use Drupal\ggroup\Plugin\views\argument\GroupIdDepth;
use Drupal\views\Annotation\ViewsArgument;
use Drupal\views\Views;

/**
 * Argument handler for group content with depth.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("parents_content")
 */
class ParentsContent extends GroupIdDepth {
  /**
   * @inheritdoc
   */
  public function query($group_by = FALSE) {
    $table = $this->ensureMyTable();

    $definition = array(
      'table' => 'group_graph',
      'field' => 'start_vertex',
      'left_table' => $table,
      'left_field' => 'gid',
    );

    $join = Views::pluginManager('join')->createInstance('standard', $definition);
    $this->query->addRelationship('group_graph', $join, 'group_graph');

    $group = $this->query->setWhereGroup('OR', 'group_id_depth');

    foreach ($this->options['depth'] as $depth) {
      if ($depth === '-1') {
        $this->query->addWhereExpression($group, "$table.gid = :gid", [':gid' => $this->argument]);
      }
      else {
        $this->query->addWhereExpression(
          $group,
          "group_graph.end_vertex = :gid AND group_graph.hops = :hops_$depth",
          [
            ':gid' => $this->argument,
            ":hops_$depth" => $depth
          ]
        );
      }

    }
  }

}
