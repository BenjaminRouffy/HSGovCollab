<?php

namespace Drupal\group_customization\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'Group Role' condition.
 *
 * @Condition(
 *   id = "access_by_group_roles",
 *   label = @Translation("Group Roles"),
 *   context = {
 *     "group" = @ContextDefinition("entity:group", label = @Translation("Group"), required = TRUE),
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 */
class GroupRole extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Creates a new GroupRole instance.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(EntityStorageInterface $entity_storage, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')->getStorage('group_role'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = [];

    // Build a list of group type labels.
    $group_roles = $this->entityStorage->loadMultiple();
    foreach ($group_roles as $role) {
      $options[$role->id()] = $role->id();
    }

    // Show a series of checkboxes for group role selection.
    $form['group_roles'] = [
      '#title' => $this->t('Group roles'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->configuration['group_roles'],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['group_roles'] = array_filter($form_state->getValue('group_roles'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $group_roles = $this->configuration['group_roles'];

    // Format a pretty string if multiple group roles were selected.
    if (count($group_roles) > 1) {
      $last = array_pop($group_roles);
      $group_roles = implode(', ', $group_roles);
      return $this->t('The group roles is @group_types or @last', ['@group_types' => $group_roles, '@last' => $last]);
    }

    // If just one was selected, return a simpler string.
    return $this->t('The group role is @group_type', ['@group_type' => reset($group_roles)]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // If there are no group roles selected and the condition is not negated, we
    // return TRUE because it means all group roles are valid.
    if (empty($this->configuration['group_roles']) && !$this->isNegated()) {
      return TRUE;
    }

    $group = $this->getContextValue('group');
    $user = $this->getContextValue('user');

    $bypass = AccessResult::allowedIfHasPermissions($user, ['bypass group access']);

    if ($bypass->isAllowed()) {
      return TRUE;
    }

    // Check if current user has group roles.
    $user_roles = [];
    $memberships = $group->getMember($user);

    if (!empty($memberships)) {
      foreach ($memberships->getRoles() as $role) {
        if (in_array($role->id(), $this->configuration['group_roles'])) {
          $user_roles[] = $role->id();
        }
      }
    }

    return (bool) count($user_roles);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['group_roles' => []] + parent::defaultConfiguration();
  }

}
