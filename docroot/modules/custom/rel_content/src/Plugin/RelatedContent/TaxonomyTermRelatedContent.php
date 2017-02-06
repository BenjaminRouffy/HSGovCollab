<?php

namespace Drupal\plugin_type_example\Plugin\Sandwich;

use Drupal\plugin_type_example\SandwichBase;
use Drupal\rel_content\Annotation\RelatedContent;
use Drupal\rel_content\RelatedContentBase;

/**
 * Provides a taxonomy term related content plugin.
 *
 * @RelatedContent(
 *   id = "taxonomy_term_rel_content",
 *   description = @Translation("Related content by taxonomy terms.")
 * )
 */
class TaxonomyTermRelatedContent extends RelatedContentBase {

  /**
   * Place an order for a sandwich.
   *
   * This is just an example method on our plugin that we can call to get
   * something back.
   *
   * @param array $extras
   *   Array of extras to include with this order.
   *
   * @return string
   *   A description of the sandwich ordered.
   */
  public function order(array $extras) {
    $ingredients = array('ham, mustard', 'rocket', 'sun-dried tomatoes');
    $sandwich = array_merge($ingredients, $extras);
    return 'You ordered an ' . implode(', ', $sandwich) . ' sandwich. Enjoy!';
  }

}
