<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by term id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_index_gid_subgroup")
 */
class GroupIndexByGroupParent extends GroupIndexGid {

  /**
   * @var GroupInterface
   */
  public $group;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SqlEntityStorageInterface $group) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->group = $group;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('group')
    );
  }

  public function adminSummary() { }
  protected function operatorForm(&$form, FormStateInterface $form_state) { }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $argument = $this->view->argument;
    $options = [];

    if (!empty($argument)) {
      $group = reset($argument)->getValue();
      /* @var Group $group */
      $group = $this->group->load($group);

      if (!empty($group)) {
        $query = \Drupal::database()->select('group_graph', 'group_graph')
          ->fields('group_graph', ['end_vertex'])
          ->condition('start_vertex', $group->id())
          ->condition('hops', 0)
          ->execute()
          ->fetchCol();

        if (!empty($query)) {
          $subgroups = [];

          foreach ($query as $id) {
            $subgroup = $this->group->load($id);

            if (!empty($subgroup)) {
              $subgroups[$subgroup->id()] = $subgroup->label();
            }
          }

          asort($subgroups);
          $options = [$group->id() => $group->label()] + $subgroups;
        }
      }
    }

    $form_state->set('filter_options', $options);

    parent::valueForm($form, $form_state);
  }

}
