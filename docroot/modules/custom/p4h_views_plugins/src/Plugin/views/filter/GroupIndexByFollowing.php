<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group_following\GroupFollowingManagerInterface;
use Drupal\p4h_views_plugins\Sort\SortItem;
use Drupal\p4h_views_plugins\Sort\SortMachine;
use Drupal\views\Annotation\ViewsFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by term id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_index_by_following")
 */
class GroupIndexByFollowing extends GroupIndexGid  {

  /**
   * @var GroupTypeInterface
   */
  public $groupType;

  /**
   * @var GroupFollowingManagerInterface
   */
  public $manager;

  /**
   * @var \Drupal\p4h_views_plugins\Sort\SortMachine
   */
  protected $sortMachine;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,ConfigEntityStorageInterface $group_type, GroupFollowingManagerInterface $manager, SortMachine $sort_machine) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->groupType = $group_type;
    $this->manager = $manager;
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
      $container->get('group_following.manager'),
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

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $groups = [];
    $account = \Drupal::currentUser();
    $groupPermissionAccess = \Drupal::getContainer()
      ->get('group_customization.group.permission');

    foreach (array_filter($this->options['gid']) as $group_type_id) {
      $groups_id = $this->manager->getFollowedForUser($account, $group_type_id);

      foreach ($groups_id as $group_id) {
        $group = Group::load($group_id);

        /* @var AccessResult $access */
        $access = $groupPermissionAccess->checkAccessForFilter($group, $account, ['published', 'content',]);

        if ($access->isAllowed()) {
          $groups[] = new SortItem($group);
        }
      }
    }

    $form_state->set('filter_options', $this->sortMachine->sort($groups));

    parent::valueForm($form, $form_state);
  }

}
