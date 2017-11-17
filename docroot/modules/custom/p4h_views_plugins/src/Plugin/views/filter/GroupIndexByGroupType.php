<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupTypeInterface;
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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigEntityStorageInterface $group_type, SqlEntityStorageInterface $group) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->groupType = $group_type;
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
      $container->get('entity.manager')->getStorage('group_type'),
      $container->get('entity.manager')->getStorage('group')
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
          $filter_groups[$group->id()] = [
            'group' => $group,
            'label' =>\Drupal::entityManager()
              ->getTranslationFromContext($group)
              ->label(),
          ];
        }
      }
    }

    $weight_table = [
      'geographical' => [
        // Start with non-geographical objects.
        '0' => 1,
        '1' => 2,
      ],
      'group_type' => [
        'region' => 1,
        'country' => 2,
        'project' => 3,
      ],
    ];

    usort($filter_groups, function($a, $b) use ($weight_table) {
      $cmp = strnatcmp($a['label'], $b['label']);

      $workspace = [
        0 =>[
          'group' => $a['group'],
          'cmp' => $cmp,
        ],
        1 => [
          'group' => $b['group'],
          'cmp' => -$cmp,
        ],
      ];

      foreach ($workspace as $key => $target) {
        $sum = '';
        $group_type = $target['group']->getGroupType()->id();

        if ($group_type !== 'project') {
          $sum .= $weight_table['geographical'][$target['group']->field_geographical_object->getValue()[0]['value']];
        }
        else {
          // Projects not depend on geographical goes to the end.
          $sum .= 3;
        }

        $sum .= $weight_table['group_type'][$group_type];

        $sum .= ($target['cmp'] + 1);

        $workspace[$key]['sum'] = $sum;
      }
      return $workspace[0]['sum'] - $workspace[1]['sum'];
    });

    $options = [];

    foreach ($filter_groups as $group_wrapper) {
      $options[$group_wrapper['group']->id()] = $group_wrapper['label'];
    }

    $form_state->set('filter_options', $options);

    parent::valueForm($form, $form_state);
  }

}
