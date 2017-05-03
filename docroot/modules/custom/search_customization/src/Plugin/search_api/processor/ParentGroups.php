<?php

namespace Drupal\search_customization\Plugin\search_api\processor;

use Drupal\Core\TypedData\DataReferenceDefinition;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\GroupType;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Plugin\search_api\processor\Property\AggregatedFieldProperty;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Utility\Utility;
use Drupal\search_customization\Processor\TranslatableProcessorProperty;

/**
 * Adds customized aggregations of existing fields to the index.
 *
 * @SearchApiProcessor(
 *   id = "parent_groups",
 *   label = @Translation("Parent groups"),
 *   description = @Translation("Add entities parent groups."),
 *   stages = {
 *     "add_properties" = 20,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class ParentGroups extends ProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $group_types = GroupType::loadMultiple();
      foreach ($group_types as $key => $group_type) {
        $definition = [
          'label' => $this->t('Parent groups with type @group_label', ['@group_label' => $group_type->label()]),
          'description' => $this->t('A id of parent group.'),
          'type' => 'string',
          'processor_id' => $this->getPluginId(),
          'target_data_type' => $key,
        ];
        $properties['parent_groups_' . $key] = new TranslatableProcessorProperty($definition);
      }
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $item->getOriginalObject()->getValue();
    $group_contents = GroupContent::loadByEntity($item->getOriginalObject()->getValue());

    if ($group_contents) {
      $group_types = GroupType::loadMultiple();
      foreach ($group_types as $group_type_key => $group_type) {
        $fields = $this->getFieldsHelper()
          ->filterForPropertyPath($item->getFields(), NULL, 'parent_groups_' . $group_type_key);
        foreach ($fields as $field) {
          if (!$field->getDatasourceId()) {
            foreach ($group_contents as $key => $group_content) {
              if (strpos($group_content->type->getValue()[0]['target_id'], $group_type_key . '-') !== FALSE) {
                $target_id = $group_content->gid->getValue()[0]['target_id'];

                if ($group_type_key == 'country') {
                  $country = Group::load($target_id);
                  if (!isset($country->field_group_status->getValue()[0]['value']) || $country->field_group_status->getValue()[0]['value'] == 'unpublished') {
                    continue;
                  }
                }

                $field->addValue($target_id);
              }
            }
          }
        }
      }
    }
  }

}
