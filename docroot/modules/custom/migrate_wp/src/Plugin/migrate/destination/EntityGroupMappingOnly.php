<?php

namespace Drupal\migrate_wp\Plugin\migrate\destination;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EmailItem;
use Drupal\Core\Password\PasswordInterface;
use Drupal\group\Entity\Group;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "group_mapping_only"
 * )
 */
class EntityGroupMappingOnly extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    return 'group';
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $result = \Drupal::entityTypeManager()
      ->getStorage('group')
      ->loadByProperties(['label' => $row->getSourceProperty('post_title')]);
    if (empty($result)) {
      throw new MigrateException('Unable to load entity by title');
    }

    $country = array_shift($result);

    return [$country->id()];
  }


  /**
   * {@inheritdoc}
   *
   * todo remove
   */
  public function checkRequirements() {}

  /**
   * {@inheritdoc}
   */
  public function rollback(array $destination_identifier) {
    // We do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function supportsRollback() {
    return FALSE;
  }
}
