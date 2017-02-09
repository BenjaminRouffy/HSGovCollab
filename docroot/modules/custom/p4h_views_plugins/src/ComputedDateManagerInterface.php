<?php

namespace Drupal\p4h_views_plugins;


interface ComputedDateManagerInterface {

  /**
   * @param $datetime \Datetime
   * @return string
   */
  public function getTimestamp($datetime);

}
