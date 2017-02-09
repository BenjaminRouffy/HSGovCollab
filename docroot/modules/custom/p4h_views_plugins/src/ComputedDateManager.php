<?php
/**
 * @file
 * ComputedDateManager Plugin Type.
 */

namespace Drupal\p4h_views_plugins;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class ComputedDateManager extends DefaultPluginManager implements ComputedDateManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ComputedDate', $namespaces, $module_handler, 'Drupal\p4h_views_plugins\ComputedDateInterface', 'Drupal\p4h_views_plugins\Annotation\ComputedDate');
    $this->alterInfo('p4h_views_plugins_computed_date_info');
    $this->setCacheBackend($cache_backend, 'p4h_views_plugins_computed_date');
  }

  /**
   * @param $datetime \Datetime
   *
   * @return string
   */
  public function getTimestamp($datetime) {
    return $datetime->format('Y-m-d');
  }
}
