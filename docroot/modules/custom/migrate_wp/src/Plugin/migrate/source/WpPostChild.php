<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for wp user accounts.
 *
 * @MigrateSource(
 *   id = "wp_post_child"
 * )
 */
class WpPostChild extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $connection = $this->select('intra_posts', 'posts')
    ->fields('posts', ['ID', 'post_author', 'post_parent', 'post_name', 'post_title', 'post_content', 'post_date', 'post_modified', 'post_type',]);

    $connection->leftJoin('intra_posts', 'parent_posts', 'parent_posts.ID = posts.post_parent');
    $connection = $connection->fields('parent_posts', ['ID', 'post_author', 'post_parent', 'post_name', 'post_title', 'post_content', 'post_date', 'post_modified', 'post_type',]);

    $connection->leftJoin('intra_postmeta', 'postmeta', 'postmeta.post_id = posts.post_parent AND postmeta.meta_key = :progress_type', [':progress_type' => 'progress_type']);
    $connection = $connection->fields('postmeta', ['meta_value', 'meta_key']);

    $connection->condition('parent_posts.post_parent', $this->configuration['root_post'])
      ->condition('parent_posts.post_type', 'page')
      ->condition('posts.post_type', $this->configuration['post_type']);

    return $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'ID' => $this->t('Post ID'),
      'post_author' => $this->t('Author ID'),
      'post_parent' => $this->t('Parent post ID'),
      'post_title' => $this->t('Title of post'),
      'post_content' => $this->t('Body of post'),
      'post_date' => $this->t('Post date in format 2013-05-29 21:24:27'),
      'post_modified' => $this->t('Post modified in format 2013-05-29 21:24:27'),
      'post_type' => $this->t('Type of post'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'ID' => [
        'type' => 'integer',
        'alias' => 'posts',
      ],
    ];
  }

}
