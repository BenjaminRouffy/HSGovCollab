<?php

namespace Drupal\migrate_social_autopost_implementation\Plugin\migrate\source\d8;

use Drupal\migrate\Row;
use Drupal\migrate_drupal_d8\Plugin\migrate\source\d8\Node;

/**
 * Drupal 8 node source from database.
 *
 * @MigrateSource(
 *   id = "d8_node_social",
 *   source_provider = "migrate_drupal_d8"
 * )
 */
class SocialNode extends Node {

  /**
   * Static cache for bundle fields.
   *
   * @var array
   */
  protected $bundleFields = [];

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    // Get selected social.
    $query->innerJoin('node__field_social_autopost', 'autopost', 'nfd.nid = autopost.entity_id and autopost.deleted = 0 and nfd.type = autopost.bundle');

    if (isset($this->configuration['social_type'])) {
      $query->condition('autopost.field_social_autopost_value', $this->configuration['social_type']);
    }

    return $query;

  }
}
