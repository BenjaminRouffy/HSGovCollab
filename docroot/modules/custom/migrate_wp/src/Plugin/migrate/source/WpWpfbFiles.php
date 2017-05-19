<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for wp posts.
 *
 * @MigrateSource(
 *   id = "wpfb_files"
 * )
 */
class WpWpfbFiles extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    //SELECT f.file_id,
    //  f.file_name,
    //  CONCAT('public://', 'wp-content/uploads/filebase', f.file_path),
    //  f.file_mtime,
    //  CONCAT_WS('/',
    //    SUBSTRING_INDEX(SUBSTRING_INDEX(fid.keywords, ' ', 2), ' ', -1),
    //    SUBSTRING_INDEX(SUBSTRING_INDEX(fid.keywords, ' ', 1), ' ', -1)
    //  ) as file_mime
    //FROM intra_wpfb_files f
    //LEFT JOIN intra_wpfb_files_id3 fid ON fid.file_id  = f.file_id

    $connection = $this->select('intra_wpfb_files', 'f');
    $connection->join('intra_wpfb_files_id3', 'fid3', 'fid3.file_id  = f.file_id');

    $connection->addField('f', 'file_id');
    $connection->addField('f', 'file_name');
    $connection->addExpression('CONCAT(\'wp-content://\', \'filebase/\', f.file_path)', 'file_path');
    $connection->addField('f', 'file_mtime');
    $connection->addExpression('CONCAT_WS(\'/\',
    SUBSTRING_INDEX(SUBSTRING_INDEX(fid3.keywords, \' \', 2), \' \', -1),
    SUBSTRING_INDEX(SUBSTRING_INDEX(fid3.keywords, \' \', 1), \' \', -1)
    )', 'file_mime');

    $connection->orderBy('f.file_id');
    return $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'file_id' => $this->t('File ID'),
      'file_name' => $this->t('File name'),
      'file_path' => $this->t('File path'),
      'file_mtime' => $this->t('File date'),
      'file_mime' => $this->t('File mime'),
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
        'alias' => 'f',
      ],
    ];
  }

}
