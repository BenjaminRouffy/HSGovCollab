<?php

namespace Drupal\group_dashboard\Plugin\views\filter;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupRole;
use Drupal\group\Entity\GroupType;
use Drupal\group\GroupMembership;
use Drupal\group\GroupMembershipLoader;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filters by admin access.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_admin_filter")
 */
class GroupAdminFilter extends FilterPluginBase {

  /**
   * @var EntityStorageInterface
   */
  public $entityStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $entityStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entityStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('group_type')
    );
  }

  public function adminSummary() { }
  protected function operatorForm(&$form, FormStateInterface $form_state) { }
  public function canExpose() {
    return FALSE;
  }

  /**
   * Mark form as extra setting form.
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * Default settings.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['roles'] = ['default' => FALSE];

    return $options;
  }

  /**
   * Extra settings form.
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $group_types = $this->entityStorage->loadMultiple();
    $options = [];

    /* @var GroupType $group_type */
    foreach ($group_types as $group_type) {
      /* @var GroupRole $role */
      foreach ($group_type->getRoles(FALSE) as $role) {
        $options[$role->id()] = $role->label() . ' (' . $role->id() . ')';
      }
    }

    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#options' => $options,
      '#default_value' => $this->options['roles'],
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $user = \Drupal::currentUser();
    $member_gids = [];

    if (!$user->hasPermission('all access to groups')) {
      /* @var GroupMembershipLoader $membership_loader */
      $membership_loader = \Drupal::service('group.membership_loader');

      /* @var GroupMembership $group_membership */
      foreach ($membership_loader->loadByUser($user, array_keys(array_filter($this->options['roles']))) as $group_membership) {
        if (!empty($group_membership->getGroupContent()->id())) {
          // Add the groups the user is a member of to use later on.
          $member_gids[] = $group_membership->getGroup()->id();
        }
      }

      // Not show any group is user not has admin access.
      $member_gids = empty($member_gids) ? [0] : $member_gids;

      $this->query->addWhere(0, "$this->table.id", $member_gids, 'IN');
    }
  }

}
