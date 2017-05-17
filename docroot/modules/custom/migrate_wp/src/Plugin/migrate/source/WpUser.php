<?php

namespace Drupal\migrate_wp\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Source plugin for wp user accounts.
 *
 * @MigrateSource(
 *   id = "wp_user"
 * )
 */
class WpUser extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('intra_wysija_user', 'users');

    $query->leftJoin('intra_participants_database', 'participants', 'users.email=participants.email');
    $query->fields('users', [
      'email',
      'firstname',
      'lastname',
      'created_at',
      'status',
    ])->fields('participants', [
      'address',
      'phone',
      'mobile_phone',
      'organisation',
      'location',
    ]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'email' => $this->t('User mail'),
      'firstname' => $this->t('First name'),
      'lastname' => $this->t('Last name'),
      'created_at' => $this->t('Created date'),
      'status' => $this->t('User status'),
      'phone' => $this->t('User phone'),
      'mobile_phone' => $this->t('User mobile phone'),
      'location' => $this->t('Location'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'email' => [
        'type' => 'string',
        'alias' => 'users',
      ],
    ];
  }

}
