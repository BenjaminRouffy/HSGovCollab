<?php

namespace Drupal\migrate_drupal_d8\Plugin\migrate\source\d8;

use Drupal\migrate\Row;

/**
 * Drupal 8 user source from database.
 *
 * @MigrateSource(
 *   id = "d8_user",
 *   source_provider = "migrate_drupal_d8"
 * )
 */
class User extends ContentEntity {

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
    $query = $this->select('users_field_data', 'u')
      ->fields('u', [
        'uid',
        'langcode',
        'preferred_langcode',
        'name',
        'pass',
        'mail',
        'timezone',
        'status',
        'created',
        'changed',
        'access',
        'login',
        'init',
        'default_langcode',
      ]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'uid' => $this->t('User ID'),
      'langcode' => $this->t('Language (fr, en, ...)'),
      'preferred_langcode' => $this->t('Preferred language'),
      'name' => $this->t('User name'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email'),
      'timezone' => $this->t('Timezone'),
      'status' => $this->t('User status'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'access' => $this->t('User last access'),
      'login' => $this->t('User login'),
      'init' => $this->t('Initial email'),
      'default_langcode' => $this->t('Default langcode'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Get attached fields.
    if (!$this->bundleFields) {
       $this->bundleFields = $this->getFields('user', 'user');

      // Manually add roles table here.
      $this->bundleFields['roles'] = [];
    }

    // Set values.
    foreach (array_keys($this->bundleFields) as $field) {
      $uid = $row->getSourceProperty('uid');
      $row->setSourceProperty($field, $this->getFieldValues('user', $field, $uid));
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
        'alias' => 'u',
      ]
    ];
  }

}
