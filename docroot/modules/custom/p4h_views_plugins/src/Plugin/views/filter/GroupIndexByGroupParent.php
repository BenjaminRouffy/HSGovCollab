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
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['sub_depth'] = ['default' => 0];

    return $options;
  }

  /**
   * @inheritdoc
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $form['sub_depth'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Depth'),
      '#default_value' => $this->options['sub_depth'],
      '#options' => [
        '0' => $this->t('Content from target group'),
        '1' => $this->t('Subgroup 1 level'),
        '2' => $this->t('Subgroup 2 level'),
      ],
      '#description' => $this->t('The depth will match group content with hierarchy. So if you have country group "Germany" with project group "Germany project" as subgroup, and selected "Content from parent group" + "Subgroup 1 level" that will result to filter all group content from "Germany" and "Germany project" groups'),
    ];

  }

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
          ->condition('start_vertex', $group->id());

        if (is_array($this->options['sub_depth'])) {
          $depth = array_filter($this->options['sub_depth'], function ($value) {
            return $value !== 0;
          });
          $query->condition('hops', $depth, 'IN');
        }
        else {
          $query->condition('hops', 0);
        }

        $query = $query->execute()->fetchCol();

        if (!empty($query)) {
          $subgroups = [];

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
