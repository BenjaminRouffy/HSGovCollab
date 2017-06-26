<?php

namespace Drupal\simplenews_customizations\Plugin\SimplenewsRecipientHandler;

use Drupal\simplenews\Plugin\simplenews\RecipientHandler\RecipientHandlerBase;
use Drupal\simplenews\RecipientHandler\RecipientHandlerInterface;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group_following\GroupFollowingManager;

/**
 * @RecipientHandler(
 *  id = "simplenews_recipient_handler",
 *  title = @Translation("The archiver plugin ID."),
 *  description = @Translation("The archiver plugin ID."),
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

}
