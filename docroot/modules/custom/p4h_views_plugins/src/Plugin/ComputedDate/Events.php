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

    if (!empty($entity->field_date)) {
      // Get minimal start date from date list as computed date.
      $dates = self::getEventPeriod($entity->field_date);

      if (!empty($dates['start_date'])) {
        /* @var \Drupal\Core\Datetime\DrupalDateTime */
        return $dates['start_date'];
      }
    }
  }

  /**
   * Helper to get event period with start date and end date.
   *
   * @param object $ranges
   *   Field date object.
   *
   * @return array
   *   Return event's start date and end date.
   */
  public static function getEventPeriod($ranges) {
    $start_dates = [];
    $end_dates = [];

    if (!empty($ranges)) {
      // Get min start date and max end date from date ranges list.
      foreach ($ranges as $date) {
        if (isset($date->start_date)) {
          $start_dates[$date->start_date->getTimestamp()] = $date->start_date;
          $end_dates[$date->end_date->getTimestamp()] = $date->end_date;
        }
      }

      if (!empty($start_dates)) {
        $start_date = $start_dates[min(array_keys($start_dates))];
        $end_date = $end_dates[max(array_keys($end_dates))];

        return [
          'start_date' => $start_date,
          'end_date' => $end_date,
        ];
      }
    }
  }
}
