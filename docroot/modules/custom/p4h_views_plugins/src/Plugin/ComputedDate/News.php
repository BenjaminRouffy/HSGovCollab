<?php
/**
 * @file
 */
namespace Drupal\p4h_views_plugins\Plugin\ComputedDate;

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
   * {@inheritdoc}
   */
  public function setValue() {
    /* @var $entity EntityInterface */
    $entity = $this->getEntity();
    if (isset($entity->field_content_date)) {
      $new_date = $entity->field_content_date->date->format('d-m-Y');
      $entity->set('computed_date', $new_date);
    }
  }

}
