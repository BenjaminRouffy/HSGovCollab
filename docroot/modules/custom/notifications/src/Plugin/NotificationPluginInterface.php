<?php

namespace Drupal\notifications\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Defines an interface for Notification Plugin plugins.
 */
interface NotificationPluginInterface extends PluginInspectionInterface {

  /**
   * @param array $build
   * @param \Drupal\Core\Block\BlockPluginInterface $block
   */
  public function blockViewAlter(array &$build, BlockPluginInterface $block);

}
