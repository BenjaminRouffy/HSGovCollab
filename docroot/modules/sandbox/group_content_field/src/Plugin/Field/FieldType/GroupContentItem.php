<?php

namespace Drupal\group_content_field\Plugin\Field\FieldType;

use Drupal;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\entity_reference_revisions\EntityNeedsSaveInterface;
use Drupal\group\Entity\GroupContent;

/**
 * Plugin implementation of the 'datetime' field type.
 *
 * @FieldType(
 *   id = "group_content_item",
 *   label = @Translation("Group content item"),
 *   description = @Translation("Manage GroupContent entities on target bundles edit pages."),
 *   default_widget = "group_select",
 *   default_formatter = "group_content_list",
 * )
 */
class GroupContentItem extends FieldItemBase {
  /**
   * Saves current memberships.
   */
  protected $membershipsValues = [];

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // TODO Values from DB its to easy for us.
    $parent_entity = $this->getParent()->getParent()->getValue();

    if (!empty($parent_entity->id()) && empty($values['from_widget'])) {
      $membership_loader = \Drupal::service('group.membership_loader');
      $memberships = $membership_loader->loadByUser($parent_entity, [$this->getSetting('gid')]);

      $values['entity_gids'] = [];
      foreach ($memberships as $membership) {
        $values['entity_gids'][] = $membership->getGroup()->id();
      }
      $this->membershipsValues = $values['entity_gids'];
    }

    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'gid' => [],
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $default_value = $this->getSetting('gid');
    $element = [];
    $groupType = \Drupal::entityTypeManager()->getStorage('group_role');
    $group_roles = $groupType->loadMultiple();
    $options = [];

    foreach ($group_roles as $group_role) {
      $options[$group_role->id()] = ucfirst($group_role->getGroupTypeId()) . ': ' . $group_role->label();
    }

    $element['gid'] = [
      '#type' => 'radios',
      '#title' => $this->t('Group type'),
      '#options' => $options,
      '#default_value' => $default_value,
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['entity_gids'] = MapDataDefinition::create()
      ->setLabel(t('Entity gids'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = array(
      'columns' => array(
        'entity_gids' => array(
          'description' => 'Serialized array of gids.',
          'type' => 'blob',
          'size' => 'big',
          'serialize' => TRUE,
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
    $entity = $this->getParent()->getParent()->getValue();
    $value = $this->getValue();
    if ($update == FALSE) {
      // TODO Need user id here.
      $entity->save();
    }
    elseif (!empty($value['from_widget'])) {
      $value = $this->getValue();
      $value_gid = $value['entity_gids'];

      $this->syncMemberships($this->membershipsValues, $value_gid, $entity->id());
    }
  }

  /**
   * @param $old
   * @param $new
   * @param $entity_id
   */
  private function syncMemberships($old, $new, $entity_id) {
    $gid = $this->getSetting('gid');
    $group_type = explode('-', $gid)[0];
    $properties = [
      'type' => "$group_type-group_membership",
      'entity_id' => $entity_id,
      'group_roles' => [
         $gid,
      ],
    ];

    foreach (array_diff($old, $new) as $delete_gid) {
      $properties['gid'] = $delete_gid;
      $result = \Drupal::entityTypeManager()
        ->getStorage('group_content')
        ->loadByProperties($properties);
      foreach ($result as $group_content) {
        $group_content->delete();
      }
    }

    foreach (array_diff($new, $old) as $add_gid) {
      $properties['gid'] = $add_gid;

      $result = \Drupal::entityTypeManager()
        ->getStorage('group_content')
        ->loadByProperties($properties);

      if (empty($result)) {
        $membership = GroupContent::create($properties)->save();
      }
    }
  }
}
