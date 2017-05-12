<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for wp user accounts.
 *
 * @MigrateSource(
 *   id = "wp_country"
 * )
 */
class WpCountry extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $connection = $this->select('intra_posts', 'posts');
    $connection = $connection->fields('posts', ['post_name', 'post_title']);
    // 386 == root page for all countries.
    $connection->condition('posts.post_parent', 386)
      ->condition('posts.post_type', 'page');
    return $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'post_name' => $this->t('Machine name'),
      'post_title' => $this->t('Title'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'post_name' => [
        'type' => 'string',
        'alias' => 'posts',
      ],
    ];
  }

}
