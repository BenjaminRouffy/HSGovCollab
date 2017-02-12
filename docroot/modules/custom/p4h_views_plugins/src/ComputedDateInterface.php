<?php

namespace Drupal\p4h_views_plugins;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface ComputedDateInterface extends PluginInspectionInterface {

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getValue();

}
