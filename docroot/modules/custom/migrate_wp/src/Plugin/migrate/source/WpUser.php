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
    $query = $this->select('intra_users', 'users');

    $query->leftJoin('intra_participants_database', 'participants', 'users.user_email=participants.email');
    $query->leftJoin('intra_wysija_user', 'wysija', 'users.user_email=wysija.email');

    $query->leftJoin('intra_usermeta', 'intra_usermeta', 'intra_usermeta.user_id = users.ID AND intra_usermeta.meta_key = :meta_key', [':meta_key' => 'organisation']);
    $query = $query->fields('intra_usermeta', ['meta_value', 'meta_key']);

    $query->fields('users', [
      'ID',
      'user_email',
      'user_registered',
    ])->fields('participants', [
      'address',
      'phone',
      'mobile_phone',
      'location',
    ])->fields('wysija', [
      'firstname',
      'lastname',
    ]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'ID' => $this->t('User ID'),
      'user_email' => $this->t('User mail'),
      'firstname' => $this->t('First name'),
      'lastname' => $this->t('Last name'),
      'user_registered' => $this->t('Created date'),
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
      'ID' => [
        'type' => 'integer',
        'alias' => 'users',
      ],
    ];
  }

}
