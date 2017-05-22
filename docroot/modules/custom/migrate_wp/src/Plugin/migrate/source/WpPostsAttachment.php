<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for wp posts.
 *
 * @MigrateSource(
 *   id = "posts_attachment"
 * )
 */
class WpPostsAttachment extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    //SELECT
    //  ip.post_id as file_id,
    //  SUBSTRING_INDEX(ip.meta_value, '/', -1) as file_name,
    //  ip.meta_value as file_path,
    //  UNIX_TIMESTAMP(t.post_date) as file_mtime,
    //  t.post_mime_type as file_mime
    //FROM intra_posts t
    //LEFT JOIN intra_postmeta ip ON ip.post_id = t.ID
    //WHERE t.post_type = 'attachment' AND ip.meta_key = '_wp_attached_file'

    $connection = $this->select('intra_posts', 't');
    $connection->join('intra_postmeta', 'ip', 'ip.post_id = t.ID');

    $connection->addField('ip', 'post_id', 'post_id');
    $connection->addField('t', 'post_title', 'post_title');
    $connection->addField('ip', 'post_id', 'file_id');
    $connection->addExpression('SUBSTRING_INDEX(ip.meta_value, \'/\', -1)', 'file_name');
    $connection->addExpression('CONCAT(\'wp-content://\', ip.meta_value)', 'file_path');
    $connection->addExpression('UNIX_TIMESTAMP(t.post_date)', 'file_mtime');
    $connection->addExpression('DATE_FORMAT(t.post_date, \'%Y-%m-%d\')', 'file_strtime');
    $connection->addField('t', 'post_mime_type', 'file_mime');
    $connection->addField('t', 'post_author', 'user_id');
    $connection->condition('t.post_type', 'attachment');
    $connection->condition('ip.meta_key', '_wp_attached_file');
    $connection->condition('t.post_mime_type', 'image/%', 'NOT LIKE');
//    $connection->range(0,100);
    $connection->orderBy('t.ID');
    return $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'file_id' => $this->t('File ID'),
      'post_title' => $this->t('Post Title'),
      'file_name' => $this->t('File name'),
      'file_path' => $this->t('File path'),
      'file_mtime' => $this->t('File date'),
      'file_strtime' => $this->t('File str date'),
      'file_mime' => $this->t('File mime'),
      'user_id' => $this->t('Post Author'),

    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'post_id' => [
        'type' => 'integer',
        'alias' => 'ip',
      ],
    ];
  }

}
