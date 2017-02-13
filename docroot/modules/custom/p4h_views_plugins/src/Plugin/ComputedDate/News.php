<?php

namespace Drupal\p4h_views_plugins\Plugin\ComputedDate;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\p4h_views_plugins\ComputedDateBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * @ComputedDate(
 *   id = "news",
 *   name = @Translation("News"),
 * )
 */
class News extends ComputedDateBase {

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getValue() {
    /* @var $entity EntityInterface */
    $entity = $this->getEntity();
    if (!empty($entity->field_content_date) && isset($entity->field_content_date->date)) {
      /* @var \Drupal\Core\Datetime\DrupalDateTime */
      return $entity->field_content_date->date;
    }
  }

}
