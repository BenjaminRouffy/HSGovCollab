<?php

namespace Drupal\migrate_drupal_d8\Plugin\migrate\source\d8;

use Drupal\Core\Database\StatementInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Base class for D8 source plugins which need to collect field values from
 * the Field API.
 */
abstract class ContentEntity extends SqlBase {

  /**
   * Returns all non-deleted field instances attached to a specific entity type.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string|null $bundle
   *   (optional) The bundle.
   *
   * @todo
   *   I am not sure that we even need that. Would we be doing it wrong if
   *   we relied only on table names?
   *
   * @return array[]
   *   The field instances, keyed by field name.
   */
  protected function getFields($entity_type, $bundle = NULL) {
    $fields_conf = $this->select('config', 'c')
      ->fields('c')
      ->condition('name', 'field.field.' . $entity_type . '.%', 'LIKE')
      ->execute()
      ->fetchAllAssoc('name');

    $fields = [];
    foreach ($fields_conf as $conf) {
      $data = unserialize($conf['data']);
      if ($data['status'] && $data['bundle'] == $bundle) {
        $fields[$data['field_name']] = $data;
      }
    }

    return $fields;
  }

  /**
   * Retrieves field values for a single field of a single entity.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $field_name
   *   The field name.
   * @param int $entity_id
   *   The entity ID.
   * @param int|null $revision_id
   *   (optional) The entity revision ID.
   *
   * @return array
   *   The raw field values, keyed by delta.
   *
   * @todo Support multilingual field values.
   */
  protected function getFieldValues($entity_type, $field_name, $entity_id, $revision_id = NULL) {
    $table = $this->getDedicatedDataTableName($entity_type, $field_name);

    $query = $this->select($table, 't')
      ->fields('t')
      ->condition('entity_id', $entity_id)
      ->condition('deleted', 0);

    if (isset($revision_id)) {
      $query->condition('revision_id', $revision_id);
    }

    $values = [];
    foreach ($query->execute() as $row) {
      foreach ($row as $key => $value) {
        $delta = $row['delta'];
        if (strpos($key, $field_name) === 0) {
          $column = substr($key, strlen($field_name) + 1);
          $values[$delta][$column] = $value;
        }
      }
    }
    return $values;
  }

  /**
   * Get the table name keeping in mind the hashing logic for long names.
   *
   * @param string $entity_type
   *   Entity type id.
   * @param string $field_name
   *   Field name.
   * @param bool $revision
   *   If revision table or not.
   *
   * @see \Drupal\Core\Entity\Sql\DefaultTableMapping::generateFieldTableName
   *
   * @throws \Exception
   *
   * @return string
   *   The table name string.
   */
  protected function getDedicatedDataTableName($entity_type, $field_name, $revision = FALSE) {
    $separator = $revision ? '_revision__' : '__';
    $table_name = $entity_type . $separator . $field_name;

    if (strlen($table_name) > 48) {
      $separator = $revision ? '_r__' : '__';

      $query = $this->select('config', 'c')
        ->fields('c', ['data'])
        ->condition('name', "field.storage.{$entity_type}.{$field_name}");
      $field_definition_data = $query->execute()->fetchField();

      if ($field_definition_data) {
        $data = unserialize($field_definition_data);
        $uuid = $data['uuid'];
      }
      else {
        throw new \Exception(sprintf('Missing field storage config for field %s', $field_name));
      }

      $entity_type = substr($entity_type, 0, 34);
      $field_hash = substr(hash('sha256', $uuid), 0, 10);
      $table_name = $entity_type . $separator . $field_hash;
    }
    return $table_name;
  }

}
