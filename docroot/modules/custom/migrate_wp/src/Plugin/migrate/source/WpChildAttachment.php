<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;

/**
 * Source plugin for wp user accounts.
 *
 * @MigrateSource(
 *   id = "wp_child_attachment"
 * )
 */
class WpChildAttachment extends WpPostChild {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->condition('posts.post_mime_type', 'image/%', 'NOT LIKE');

    return $query;
  }

}
