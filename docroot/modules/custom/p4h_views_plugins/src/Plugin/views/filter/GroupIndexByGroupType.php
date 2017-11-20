<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\p4h_views_plugins\Sort\SortItem;
use Drupal\p4h_views_plugins\Sort\SortMachine;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by term id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_index_gid")
 */
class GroupIndexByGroupType extends GroupIndexGid  {

  /**
   * @var GroupTypeInterface
   */
  public $groupType;

  /**
   * @var GroupInterface
   */
  public $group;

  /**
   * @var \Drupal\p4h_views_plugins\Sort\SortMachine
   */
  public $sortMachine;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigEntityStorageInterface $group_type, SqlEntityStorageInterface $group, SortMachine $sort_machine) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->groupType = $group_type;
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
      $container->get('entity.manager')->getStorage('group_type'),
      $container->get('entity.manager')->getStorage('group'),
      $container->get('p4h_views_plugins.sort_machine')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['gid'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $group_type = $this->groupType->loadMultiple();
    $options = [];

    foreach ($group_type as $entity_type) {
      $options[$entity_type->id()] = $entity_type->label();
    }

    $form['gid'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Group type'),
      '#options' => $options,
      '#default_value' => $this->options['gid'],
      '#required' => TRUE,
    ];
  }


  protected function valueForm(&$form, FormStateInterface $form_state) {
    $filter_groups = [];
    $account = \Drupal::currentUser();
    $types = [];
    $is_anonymous = \Drupal::currentUser()->isAnonymous();

    if ($is_anonymous) {
      parent::valueForm($form, $form_state);
      return;
    }

    if (!empty($this->view->filter['type'])) {
      $types = $this->view->filter['type']->value;
    }

    foreach (array_filter($this->options['gid']) as $group_type_id) {
      $group_type = $this->groupType->load($group_type_id);

      $query = \Drupal::entityQuery('group')
        // @todo Sorting on vocabulary properties -
        //   https://www.drupal.org/node/1821274.
        ->addTag('group_access');
      $query->condition('type', $group_type->id());
      $result = $query->execute();
      $groups = $this->group->loadMultiple($result);

      $groupPermissionAccess = \Drupal::getContainer()
        ->get('group_customization.group.permission');

      /* @var Group $group */
      foreach ($groups as $group) {
        /* @var AccessResult $access */
        $access =  $groupPermissionAccess->checkAccessForFilter($group, $account, [
          'published',
          'content',
        ]);

        if (!empty($types)) {
          $query = \Drupal::database()->select('group_content_field_data', 'group_content_field_data')
            ->fields('group_content_field_data', ['id'])
            ->condition('gid', $group->id());

          if ($is_anonymous) {
            $query->leftJoin('node_field_data', 'node_field_data', 'group_content_field_data.entity_id=node_field_data.nid');
            $query->condition('node_field_data.public_content', 1);
          }

          $or_condition = $query->orConditionGroup();

          foreach ($types as $type) {
            $or_condition->condition('group_content_field_data.type', '%' . $query->escapeLike($type), 'LIKE');
          }

          $result = $query->condition($or_condition)
            ->execute()
            ->fetchCol();
        }

        if ($access->isAllowed() && !empty($result)) {
          $filter_groups[] = new SortItem($group);
        }
      }
    }

    $form_state->set('filter_options', $this->sortMachine->sort($filter_groups));

    parent::valueForm($form, $form_state);
  }

}
