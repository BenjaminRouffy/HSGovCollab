<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for wp wpfb filess.
 *
 * @MigrateSource(
 *   id = "wp_wpfb_country_file"
 * )
 */
class WpWpfbCountryFile extends SqlBase {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $connection = $this->select('intra_wpfb_files', 'files')
      ->fields('files');

    $connection->leftJoin('intra_wpfb_cats', 'categories', 'categories.cat_id = files.file_category');
    $connection = $connection->fields('categories');

    $or = $connection->orConditionGroup();
    $or->condition('categories.cat_path', 'africa/%', 'LIKE');
    $or->condition('categories.cat_path', 'asia/%', 'LIKE');
    $or->condition('categories.cat_path', 'america/%', 'LIKE');
    $or->condition('categories.cat_path', 'europe/%', 'LIKE');
    $connection->condition($or);

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
