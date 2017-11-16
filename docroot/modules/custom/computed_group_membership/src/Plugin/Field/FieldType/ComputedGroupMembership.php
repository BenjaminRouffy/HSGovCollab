<?php

namespace Drupal\computed_group_membership\Plugin\Field\FieldType;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\group\Entity\Group;

/**
 * Plugin implementation.
 *
 * @FieldType(
 *   id = "computed_group_membership",
 *   label = @Translation("Computed group membership"),
 *   category = @Translation("Text"),
 *   default_formatter = "list_computed_group_membership",
 * )
 */
class ComputedGroupMembership extends FieldItemBase {

  /**
   * Whether or not the value has been calculated.
   *
   * @var bool
   */
  protected $isCalculated = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    $this->ensureCalculated();
    return parent::__get($name);
  }
  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $this->ensureCalculated();
    return parent::isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $this->ensureCalculated();
    return parent::getValue();
  }

  /**
   * Calculates the value of the field and sets it.
   */
  protected function ensureCalculated() {
    if (!$this->isCalculated) {
      $entity = $this->getEntity();
      if (!$entity->isNew()) {
        $result = $p4h_area_values = $default_values = [];

        foreach([
          'field_country_manager' => t('Country Focal Person'),
          'field_project_manager' => t('Project manager'),
        ] as $field_name => $text){
          $value = $entity->get($field_name)->getValue();

          if (!empty($value[0]['entity_gids'])) {
            foreach(Group::loadMultiple($value[0]['entity_gids']) as $group) {
              if (!$group->field_geographical_object->value) {
                $p4h_area_values[] = $group->label() . ' ' . t('P4H Area manager');
              }
              else {
                $default_values[] = $group->label() . ' ' . $text;
              }
            }
          }
        }

        $result['values'] = array_merge($p4h_area_values, $default_values);

        $this->setValue($result);
      }
      $this->isCalculated = TRUE;
    }
  }

  /**
   * @inheritdoc
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['values'] = MapDataDefinition::create()
      ->setLabel(t('Values'));

    return $properties;
  }

  /**
   * @inheritdoc
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'values' => [
          'description' => 'Serialized array of values.',
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
        ],
      ],
    ];
  }
}
