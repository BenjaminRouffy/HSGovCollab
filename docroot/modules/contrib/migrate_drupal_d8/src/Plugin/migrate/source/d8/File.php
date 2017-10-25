<?php

namespace Drupal\migrate_drupal_d8\Plugin\migrate\source\d8;

use Drupal\migrate\Row;

/**
 * Drupal 8 file source from database.
 *
 * @MigrateSource(
 *   id = "d8_file",
 *   source_provider = "migrate_drupal_d8"
 * )
 */
class File extends ContentEntity {

  /**
   * Static cache for bundle fields.
   *
   * @var array
   */
  protected $bundleFields = [];

  /**
   * {@inheritdoc}
   */
  public function query() {
    // @todo Do we need uuid here too?
    $query = $this->select('file_managed', 'f')
      ->fields('f', [
        'fid',
        'langcode',
        'uid',
        'filename',
        'uri',
        'filemime',
        'filesize',
        'status',
        'created',
        'changed',
        'type',
      ]);

    // We support file entity too.
    if (isset($this->configuration['bundle']) && $this->getDatabase()->schema()->fieldExists('file_managed', 'type')) {
      $query->condition('f.type', $this->configuration['bundle']);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'fid' => $this->t('File ID'),
      'langcode' => $this->t('Language (fr, en, ...)'),
      'uid' => $this->t('Preferred language'),
      'filename' => $this->t('File name'),
      'uri' => $this->t('File uri'),
      'filemime' => $this->t('Filemime'),
      'filesize' => $this->t('Filesize'),
      'status' => $this->t('Status'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'type' => $this->t('File type'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get attached fields.
    if (!$this->bundleFields) {
      $this->bundleFields = $this->getFields('file', $row->getSourceProperty('bundle'));
    }

    // Set values.
    foreach (array_keys($this->bundleFields) as $field) {
      $fid = $row->getSourceProperty('fid');
      $row->setSourceProperty($field, $this->getFieldValues('file', $field, $fid));
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'fid' => [
        'type' => 'integer',
        'alias' => 'f',
      ]
    ];
  }

}
