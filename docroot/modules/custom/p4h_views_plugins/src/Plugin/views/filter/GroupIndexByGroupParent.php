<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\p4h_views_plugins\Sort\SortItem;
use Drupal\p4h_views_plugins\Sort\SortMachine;
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
   * @var \Drupal\p4h_views_plugins\Sort\SortMachine
   */
  protected $sortMachine;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SqlEntityStorageInterface $group, SortMachine $sort_machine) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->group = $group;
    $this->sortMachine = $sort_machine;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('group'),
      $container->get('p4h_views_plugins.sort_machine')
    );
  }

  public function adminSummary() { }
  protected function operatorForm(&$form, FormStateInterface $form_state) { }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $argument = $this->view->argument;
    $groups = [];

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
          foreach ($query as $id) {
            $subgroup = $this->group->load($id);

            if (!empty($subgroup)) {
              $groups[] = new SortItem($subgroup);
            }
          }

          $groups[] = new SortItem($group);
        }
      }
    }

    $form_state->set('filter_options', $this->sortMachine->sort($groups));

    parent::valueForm($form, $form_state);
  }

}
