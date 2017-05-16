<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for wp posts.
 *
 * @MigrateSource(
 *   id = "wp_post"
 * )
 */
class WpPost extends SqlBase {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $connection = $this->select('intra_posts', 'posts');
    $connection = $connection->fields('posts', ['post_name', 'post_title']);
    $connection->condition('posts.post_parent', $this->configuration['root_post'])
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
