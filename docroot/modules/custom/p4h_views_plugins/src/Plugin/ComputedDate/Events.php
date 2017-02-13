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
    if (!empty($entity->field_date) && isset($entity->field_date->start_date)) {
      /* @var \Drupal\Core\Datetime\DrupalDateTime */
      return $entity->field_date->start_date;
    }
  }

}
