<?php

namespace Drupal\migrate_drupal_d8\Plugin\migrate\source\d8;

use Drupal\migrate\Row;

/**
 * Drupal 8 node source from database.
 *
 * @MigrateSource(
 *   id = "d8_taxonomy_term",
 *   source_provider = "migrate_drupal_d8"
 * )
 */
class TaxonomyTerm extends ContentEntity {

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
    $query = $this->select('taxonomy_term_field_data', 'tfd')
      ->fields('tfd', [
        'tid',
        'vid',
        'langcode',
        'name',
        'description__value',
        'description__format',
        'weight',
        'changed',
        'default_langcode',
      ]);
    $query->addField('t', 'uuid');
    $query->innerJoin('taxonomy_term_data', 't', 'tfd.vid = t.vid');

    if (isset($this->configuration['bundle'])) {
      $query->condition('tfd.vid', $this->configuration['bundle']);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'tid' => $this->t('Term ID'),
      'vid' => $this->t('Vocabulary id'),
      'langcode' => $this->t('Language (fr, en, ...)'),
      'name' => $this->t('Name'),
      'description__value' => $this->t('Description value'),
      'description__format' => $this->t('Description format'),
      'wight' => $this->t('Weight'),
      'changed' => $this->t('Modified timestamp'),
      'default_langcode' => $this->t('Default langcode'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get Field API field values.
    if (!$this->bundleFields) {
      $this->bundleFields = $this->getFields('taxonomy_term', $row->getSourceProperty('bundle'));
    }

    $tid = $row->getSourceProperty('tid');
    foreach (array_keys($this->bundleFields) as $field) {
      $row->setSourceProperty($field, $this->getFieldValues('taxonomy_term', $field, $tid));
    }

    if ($parent = $this->taxonomyTermGetParent($tid)) {
      $row->setSourceProperty('parent', $parent);
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tid' => [
        'type' => 'integer',
        'alias' => 'tfd',
      ]
    ];
  }

  protected function taxonomyTermGetParent($tid) {
    /** @var \Drupal\Core\Database\Query\SelectInterface $query */
    $query = $this->select('taxonomy_term_hierarchy', 'h')
      ->fields('h', ['parent'])
      ->condition('tid', $tid);

    if ($tid = $query->execute()->fetchField()) {
      return $tid;
    }
    else {
      return NULL;
    }
  }
}
