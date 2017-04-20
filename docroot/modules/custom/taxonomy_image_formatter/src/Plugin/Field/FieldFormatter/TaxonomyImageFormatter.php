<?php

namespace Drupal\taxonomy_image_formatter\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;


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
        if ($entity->hasField('field_organisation')) {
          $organisation = $entity->get('field_organisation')->getValue();

          if (!empty($organisation)) {
            $organisation = reset($organisation);
            // Load organisation taxonomy term.
            $term = Term::load($organisation['target_id']);
            if ($term && $term->hasField('field_organisation_image')) {
              $organisation_image_items = $term->get('field_organisation_image');
              if (!$organisation_image_items->isEmpty()) {
                $elements[0]['#item'] = $organisation_image_items->first();
              }
            }
          }
        }
      }
    }

    return $elements;
  }
}
