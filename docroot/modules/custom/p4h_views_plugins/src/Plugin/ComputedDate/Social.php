<?php

namespace Drupal\p4h_views_plugins\Plugin\ComputedDate;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\p4h_views_plugins\ComputedDateBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * @ComputedDate(
 *   id = "social",
 *   name = @Translation("Social"),
 * )
 */
class Social extends ComputedDateBase {

  /**
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getValue() {
    /* @var $entity EntityInterface */
    $entity = $this->getEntity();

    if (!empty($entity->created)) {
      return DrupalDateTime::createFromTimestamp($entity->created->getValue()[0]['value']);
    }
  }

}
