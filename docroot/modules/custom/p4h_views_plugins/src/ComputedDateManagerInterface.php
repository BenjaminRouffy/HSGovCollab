<?php

namespace Drupal\p4h_views_plugins;

/**
 * Interface ComputedDateManagerInterface.
 */
interface ComputedDateManagerInterface {

  /**
   * Entrypoint to get a date format from a Datetime object.
   *
   * @param \Datetime $datetime
   *   Datetime object.
   *
   * @return string
   *   Date in specific format.
   */
  public function getTimestamp($datetime);

}
