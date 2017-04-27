<?php

namespace Drupal\p4h_views_plugins\Plugin\ComputedDate;

use Drupal\p4h_views_plugins\ComputedDateBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * @ComputedDate(
 *   id = "event",
 *   name = @Translation("Events"),
 * )
 */
class Events extends ComputedDateBase {

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getValue() {
    /* @var $entity EntityInterface */
    $entity = $this->getEntity();

    $dates = [];

    if (!empty($entity->field_date)) {
      // Get minimal start date from date list as computed date.
      foreach ($entity->field_date as $date) {
        if (isset($date->start_date)) {
          $dates[$date->start_date->getTimestamp()] = $date->start_date;
        }
      }

      if (!empty($dates)) {
        $start_date = $dates[min(array_keys($dates))];
        /* @var \Drupal\Core\Datetime\DrupalDateTime */
        return $start_date;
      }
    }
  }

}
