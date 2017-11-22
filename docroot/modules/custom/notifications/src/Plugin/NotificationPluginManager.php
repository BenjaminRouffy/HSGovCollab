<?php

namespace Drupal\notifications\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Notification Plugin plugin manager.
 */
class NotificationPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new NotificationPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/NotificationPlugin', $namespaces, $module_handler, 'Drupal\notifications\Plugin\NotificationPluginInterface', 'Drupal\notifications\Annotation\NotificationPlugin');

    $this->alterInfo('notifications_notification_plugin_info');
    $this->setCacheBackend($cache_backend, 'notifications_notification_plugin_plugins');
  }

  /**
   * @param $string
   *
   * @return object
   */
  public function get($string) {
    return $this->createInstance($string);
  }

}
