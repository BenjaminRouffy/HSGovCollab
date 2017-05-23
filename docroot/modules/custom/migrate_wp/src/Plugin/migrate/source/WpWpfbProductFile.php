<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for wp wpfb filess.
 *
 * @MigrateSource(
 *   id = "wp_wpfb_product_file"
 * )
 */
class WpWpfbProductFile extends SqlBase {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $connection = $this->select('intra_wpfb_files', 'files')
      ->fields('files');

    $connection->leftJoin('intra_wpfb_cats', 'categories', 'categories.cat_id = files.file_category');
    $connection = $connection->fields('categories');

    $connection->condition('categories.cat_path', 'global/cpd%', 'LIKE');

    return $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'file_id',
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'file_id' => [
        'type' => 'integer',
        'alias' => 'files',
      ],
    ];
  }

}
