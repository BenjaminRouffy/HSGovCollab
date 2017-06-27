<?php

namespace Drupal\simplenews_customizations\Plugin\simplenews\RecipientHandler;

use Drupal\simplenews\Plugin\simplenews\RecipientHandler\RecipientHandlerBase;
use Drupal\simplenews\RecipientHandler\RecipientHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group_following\GroupFollowingManager;

/**
 * @RecipientHandler(
 *  id = "simplenews_recipient_handler",
 *  title = @Translation("Simplenews by type."),
 *  description = @Translation("Simplenews by type."),
 *  types = {
 *    "default1"
 *  }
 * )
 */
class SimplenewsByTypeRecipientHandler extends RecipientHandlerBase implements RecipientHandlerInterface, ContainerFactoryPluginInterface {

  /**
   * Drupal\group_following\GroupFollowingManager definition.
   *
   * @var \Drupal\group_following\GroupFollowingManager
   */
  protected $groupFollowingManager;

  /**
   * Constructs a new SimplenewsByTypeRecipientHandler object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GroupFollowingManager $group_following_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->groupFollowingManager = $group_following_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('group_following.manager')
    );
  }

  /**
   *
   */
  public function buildRecipientQuery() {
    $select = db_select('users_field_data', 'u');
    $select->fields('u', ['uid']);
    $select->join('user__roles', 'ur', db_and()
      ->where('u.uid = ur.entity_id')
    );
    $select->fields('ur', ['roles_target_id']);

    $select->leftJoin('simplenews_subscriber', 's', db_and()
      ->where('u.mail = s.mail')
    );
    $select->addExpression('ifnull(s.status, 1)', 'status');
    return $select;
  }

  /**
   *
   */
  public function buildRecipientCountQuery() {
    return $this->buildRecipientQuery()->countQuery();
  }

  /**
   *
   */
  public function count() {
    return parent::count();
  }

  /**
   *
   */
  public function settingsForm($element, $settings) {

    $element['type'] = [
      '#title' => t('Type'),
      '#type' => 'select',
    // user_roles(TRUE),
      '#options' => [],
    ];

    // Add some text to describe the send situation.
    $subscriber_count = $this->count();
    $element['count'] = [
      '#type' => 'item',
      '#markup' => t('Send newsletter issue to @count subscribers.', ['@count' => $subscriber_count]),
    ];
    return $element;
  }

}
