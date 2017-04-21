<?php

namespace Drupal\taxonomy_image_formatter\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\taxonomy_image_formatter\TaxonomyImageTrait;


/**
 * Plugin implementation of the 'taxonomy_image_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "taxonomy_image_formatter",
 *   label = @Translation("Taxonomy Image"),
 *   field_types = {
 *     "image"
 *   },
 * )
 */
class TaxonomyImageFormatter extends ImageFormatter {

  use TaxonomyImageTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $entity = $items->getEntity();

    if ($entity instanceof User) {
      // Change empty user avatar to organisation term image.
      $avatar = $entity->get('field_avatar')->getValue();

      if (!$avatar) {
        $term_image = $this->getTermImage($entity);

        if ($term_image) {
          $elements[0]['#item'] = $term_image;
        }
      }
    }

    return $elements;
  }
}
