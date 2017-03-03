<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

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
      '#type' => 'radios',
      '#title' => $this->t('Group type'),
      '#options' => $options,
      '#default_value' => $this->options['gid'],
      '#required' => TRUE,
    ];
  }


  protected function valueForm(&$form, FormStateInterface $form_state) {
    $group_type = $this->groupType->load($this->options['gid']);
    $options = [];

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
    $form_state->set('filter_options', $options);

    parent::valueForm($form, $form_state);
  }

}
