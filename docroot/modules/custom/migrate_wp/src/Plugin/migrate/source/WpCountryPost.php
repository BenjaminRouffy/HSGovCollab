<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Source plugin for wp user accounts.
 *
 * @MigrateSource(
 *   id = "wp_country_post"
 * )
 */
class WpCountryPost extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $connection = $this->select('intra_posts', 'posts')
    ->fields('posts', ['ID', 'post_author', 'post_parent', 'post_title', 'post_content', 'post_date', 'post_modified', 'post_type',]);
    $connection->leftJoin('intra_posts', 'parent_posts', 'parent_posts.ID = posts.post_parent');
    // 386 == root page for all countries.
    $connection->condition('parent_posts.post_parent', 386)
      ->condition('parent_posts.post_type', 'page')
      ->condition('posts.post_type', 'page');
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

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    /**
     * prepareRow() is the most common place to perform custom run-time
     * processing that isn't handled by an existing process plugin. It is called
     * when the raw data has been pulled from the source, and provides the
     * opportunity to modify or add to that data, creating the canonical set of
     * source data that will be fed into the processing pipeline.
     *
     * In our particular case, the list of a user's favorite beers is a pipe-
     * separated list of beer IDs. The processing pipeline deals with arrays
     * representing multi-value fields naturally, so we want to explode that
     * string to an array of individual beer IDs.
     */
    if ($value = $row->getSourceProperty('beers')) {
      $row->setSourceProperty('beers', explode('|', $value));
    }
    /**
     * Always call your parent! Essential processing is performed in the base
     * class. Be mindful that prepareRow() returns a boolean status - if FALSE
     * that indicates that the item being processed should be skipped. Unless
     * we're deciding to skip an item ourselves, let the parent class decide.
     */
    return parent::prepareRow($row);
  }

}
