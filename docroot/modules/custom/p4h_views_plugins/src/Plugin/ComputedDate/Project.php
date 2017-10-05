<?php

namespace Drupal\p4h_views_plugins\Plugin\ComputedDate;

use Drupal\p4h_views_plugins\ComputedDateBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * @ComputedDate(
 *   id = "project",
 *   name = @Translation("Collaboration"),
 * )
 */
class Project extends ComputedDateBase {

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getValue() {
    /* @var $entity EntityInterface */
    $entity = $this->getEntity();

    if (!empty($entity->field_date) && isset($entity->field_date->date)) {
      /* @var \Drupal\Core\Datetime\DrupalDateTime */
      return $entity->field_date->date;
    }
  }

}
