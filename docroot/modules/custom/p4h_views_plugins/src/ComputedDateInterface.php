<?php
/**
 * @file
 * Provides Drupal\p4h_views_plugins\ComputedDateInterface
 */

namespace Drupal\p4h_views_plugins;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface ComputedDateInterface extends PluginInspectionInterface {

  /**
   * {@inheritdoc}
   */
  public function setValue();

}
