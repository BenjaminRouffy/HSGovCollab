<?php

namespace Drupal\taxonomy_image_formatter;

use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

/**
 * Trait for Taxonomy term image.
 */
trait TaxonomyImageTrait {
  /**
   * Get taxonomy term image.
   *
   * @param \Drupal\user\Entity\User $user
   *   User entity.
   *
   * @return bool|\Drupal\Core\TypedData\TypedDataInterface
   *   Return image if exists.
   */
  public function getTermImage(User $user) {
    if ($user->hasField('field_organisation')) {
      $organisation = $user->get('field_organisation')->getValue();

      if (!empty($organisation)) {
        $organisation = reset($organisation);
        // Load organisation taxonomy term.
        $term = Term::load($organisation['target_id']);
        if ($term && $term->hasField('field_organisation_image')) {
          $image_items = $term->get('field_organisation_image');
          if (!$image_items->isEmpty()) {
            return $image_items->first();
          }
        }
      }
    }
    return FALSE;
  }
}
