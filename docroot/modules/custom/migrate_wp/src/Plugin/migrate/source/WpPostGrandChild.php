<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for wp user accounts.
 *
 * @MigrateSource(
 *   id = "wp_post_grandchild"
 * )
 */
class WpPostGrandChild extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('intra_posts', 'posts');
    $query->leftJoin('intra_posts', 'parent', 'parent.ID = posts.post_parent');
    $query->leftJoin('intra_posts', 'grand_parent', 'grand_parent.ID = parent.post_parent');

    $query->fields('posts', [
      'ID',
      'post_author',
      'post_parent',
      'post_name',
      'post_title',
      'post_content',
      'post_date',
      'post_modified',
      'post_type',
    ])->fields('parent', [
      'ID',
      'post_author',
      'post_parent',
      'post_name',
      'post_title',
      'post_content',
      'post_date',
      'post_modified',
      'post_type',
    ])->fields('grand_parent', [
      'ID',
      'post_author',
      'post_parent',
      'post_name',
      'post_title',
      'post_content',
      'post_date',
      'post_modified',
      'post_type',
    ]);

    $query->condition('parent.post_type', 'page')
      ->condition('grand_parent.post_type', 'page')
      ->condition('posts.post_type', $this->configuration['post_type'])
      ->condition('grand_parent.post_parent', $this->configuration['root_post']);

    return $query;
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
