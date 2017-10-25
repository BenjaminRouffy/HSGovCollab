<?php

namespace Drupal\migrate_drupal_d8\Plugin\migrate\source\d8;

use Drupal\migrate\Row;

/**
 * Drupal 8 node source from database.
 *
 * @MigrateSource(
 *   id = "d8_node",
 *   source_provider = "migrate_drupal_d8"
 * )
 */
class Node extends ContentEntity {

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
    $query = $this->select('node_field_data', 'nfd')
      ->fields('nfd', [
        'nid',
        'vid',
        'type',
        'title',
        'langcode',
        'uid',
        'status',
        'created',
        'changed',
        'promote',
        'sticky',
      ]);
    $query->addField('n', 'uuid');
    $query->innerJoin('node', 'n', 'nfd.vid = n.vid');

    if (isset($this->configuration['bundle'])) {
      $query->condition('nfd.type', $this->configuration['bundle']);
    }

    return $query;

  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'nid' => $this->t('Node ID'),
      'type' => $this->t('Type'),
      'langcode' => $this->t('Language (fr, en, ...)'),
      'title' => $this->t('Title'),
      'uid' => $this->t('Node authored by (uid)'),
      'status' => $this->t('Published'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'promote' => $this->t('Promoted to front page'),
      'sticky' => $this->t('Sticky at top of lists'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get Field API field values.
    if (!$this->bundleFields) {
      $this->bundleFields = $this->getFields('node', $row->getSourceProperty('bundle'));
    }

    foreach (array_keys($this->bundleFields) as $field) {
      $nid = $row->getSourceProperty('nid');
      $vid = $row->getSourceProperty('vid');
      $row->setSourceProperty($field, $this->getFieldValues('node', $field, $nid, $vid));
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ]
    ];
  }

}
