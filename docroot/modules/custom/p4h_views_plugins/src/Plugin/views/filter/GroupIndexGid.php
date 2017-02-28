<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by term id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_index_gid")
 */
class GroupIndexGid extends ManyToOne {

  public $groupType;
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
    $options['filter_type'] = ['default' => 'by_country_relation'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $group_type = $this->groupType->loadMultiple();
    $options = [];

    $form['filter_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Filter type'),
      '#options' => [
        'by_group_type' => $this->t('By group type'),
        'by_country_relation' => $this->t('By country relation'),
      ],
      '#default_value' => $this->options['filter_type'],
    ];

    foreach ($group_type as $entity_type) {
      $options[$entity_type->id()] = $entity_type->label();
    }

    $form['gid'] = [
      '#type' => 'radios',
      '#title' => $this->t('Group type'),
      '#options' => $options,
      '#default_value' => $this->options['gid'],
      '#required' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="options[filter_type]"]' => ['value' => 'by_group_type'],
        ],
      ],
    ];
  }

  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    $options = [];
    $title = $this->t('Select terms');

    switch ($this->options['filter_type']) {
      case 'by_group_type':
        $group_type = $this->groupType->load($this->options['gid']);

        if (!empty($this->options['limit'])) {
          $title = $this->t('Select group from @group list', [
            '@group' => $group_type->label(),
          ]);
        }

        $query = \Drupal::entityQuery('group')
          // @todo Sorting on vocabulary properties -
          //   https://www.drupal.org/node/1821274.
          ->addTag('group_access');
        $query->condition('type', $group_type->id());
        $groups = $this->group->loadMultiple($query->execute());

        foreach ($groups as $group) {
          $options[$group->id()] = \Drupal::entityManager()
            ->getTranslationFromContext($group)
            ->label();
        }

        asort($options);
        break;

      case 'by_country_relation':
        $argument = $this->view->argument;

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
                $subgroups[$subgroup->id()] = $subgroup->label();
              }

              asort($subgroups);
              $options = [$group->id() => $group->label()] + $subgroups;
            }
          }
        }
        break;
    }

    $default_value = (array) $this->value;

    if ($exposed = $form_state->get('exposed')) {
      $identifier = $this->options['expose']['identifier'];

      if (!empty($this->options['expose']['reduce'])) {
        $options = $this->reduceValueOptions($options);

        if (!empty($this->options['expose']['multiple']) && empty($this->options['expose']['required'])) {
          $default_value = [];
        }
      }

      if (empty($this->options['expose']['multiple'])) {
        if (
          empty($this->options['expose']['required']) &&
          (empty($default_value) || !empty($this->options['expose']['reduce']))
        ) {
          $default_value = 'All';
        }
        elseif (empty($default_value)) {
          $keys = array_keys($options);
          $default_value = array_shift($keys);
        }
        // Due to #1464174 there is a chance that array('') was saved in the admin ui.
        // Let's choose a safe default value.
        elseif ($default_value == ['']) {
          $default_value = 'All';
        }
        else {
          $copy = $default_value;
          $default_value = array_shift($copy);
        }
      }
    }

    $form['value'] = [
      '#type' => 'select',
      '#title' => $title,
      '#multiple' => TRUE,
      '#options' => $options,
      '#size' => min(9, count($options)),
      '#default_value' => $default_value,
      '#access' => !empty($options),
    ];

    $user_input = $form_state->getUserInput();

    if ($exposed && isset($identifier) && !isset($user_input[$identifier])) {
      $user_input[$identifier] = $default_value;
      $form_state->setUserInput($user_input);
    }
  }

}
