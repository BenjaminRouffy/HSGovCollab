<?php
/**
 * @file
 */

namespace Drupal\p4h_views_plugins;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;

abstract class ComputedDateBase extends PluginBase implements ComputedDateInterface {

  /**
   * @return EntityInterface
   */
  protected function getEntity() {
    return $this->configuration['entity'];
  }
}
