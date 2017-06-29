<?php

namespace Drupal\simplenews_customizations\Plugin\simplenews\RecipientHandler;

use Drupal\simplenews\RecipientHandler\RecipientHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\simplenews_customizations\RecipientHandlerBaseTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group_following\GroupFollowingManager;

/**
 * @RecipientHandler(
 *  id = "simplenews_managers",
 *  title = @Translation("Simplenews by managers."),
 *  description = @Translation("Simplenews by managers."),
 *  types = {
 *    "managers"
 *  }
 * )
 */
class SimplenewsManagersRecipientHandler extends RecipientHandlerExtraBase implements RecipientHandlerInterface, ContainerFactoryPluginInterface {

  use RecipientHandlerBaseTrait;

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
