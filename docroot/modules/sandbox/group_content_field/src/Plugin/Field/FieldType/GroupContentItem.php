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
  protected $entityGidsValues = [];
  protected $membershipLoader;
  protected $groupRole;
  protected $groupType;
  protected $pluginManager;
  protected $groupContent;


  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
    $this->membershipLoader = \Drupal::service('group.membership_loader');
    $this->groupRole = \Drupal::entityTypeManager()->getStorage('group_role');
    $this->groupType = \Drupal::entityTypeManager()->getStorage('group_type');
    $this->groupContent = \Drupal::entityTypeManager()->getStorage('group_content');
    $this->pluginManager= \Drupal::service('plugin.manager.group_content_enabler');
  }

  /**
   * Build properties.
   */
  protected function buildGroupContentProperties($entity) {
    $properties = ['type' => $this->getSetting('plugin_type'), 'entity_id' => $entity->id()];

    // TODO
    if (strpos($this->getSetting('plugin_type'), 'group_membership') !== FALSE) {
      $properties['group_roles'] = $this->getSetting('group_roles');
    }

    return $properties;
  }

  /**
   * @inheritdoc
   *
   * TODO Values from DB its to easy for us.
   */
  public function getValue() {
    $parent_entity = $this->getParent()->getParent()->getValue();

    if (!empty($parent_entity->id()) && empty($this->values['from_widget'])) {
      $properties = $this->buildGroupContentProperties($parent_entity);

      /** @var \Drupal\group\Entity\GroupContentInterface[] $group_contents */
      $group_contents = $this->groupContent->loadByProperties($properties);

      $this->values['entity_gids'] = [];
      foreach ($group_contents as $group_content) {
        $this->values['entity_gids'][] = $group_content->getGroup()->id();
      }
      $this->entityGidsValues = $this->values['entity_gids'];
    }

    return parent::getValue();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'group_roles' => [],
      'group_type' => [],
      'plugin_type' => [],
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];
    $options = [];
    $group_types = $this->groupType->loadMultiple();
    $group_type_default = $form_state->getValue('settings')['group_type'] ?? $this->getSetting('group_type');
    foreach ($group_types as $group_type) {
      $options[$group_type->id()] = $group_type->label();
    }
    $element['group_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Group type'),
      '#options' => $options,
      '#default_value' => $group_type_default,
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => 'Drupal\group_content_field\Plugin\Field\FieldType\GroupContentItem::updatePluginType',
        'wrapper' => 'edit-plugin-type-wrapper',
      ),
    ];
    if ($group_type_default) {
      $options = [];
      $plugin_type_default = $form_state->getValue('settings')['plugin_type'] ?? $this->getSetting('plugin_type');
      $plugin_types = $this->pluginManager->getInstalled($this->groupType->load($group_type_default));

      foreach ($plugin_types as $plugin_type) {
        $options[$plugin_type->getContentTypeConfigId()] = $plugin_type->getLabel();
      }

      $element['plugin_type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Plugin type'),
        '#options' => $options,
        '#default_value' => $plugin_type_default,
        '#required' => TRUE,
        '#ajax' => array(
          'callback' => 'Drupal\group_content_field\Plugin\Field\FieldType\GroupContentItem::updatePluginType',
          'wrapper' => 'edit-plugin-type-wrapper',
        ),
      ];

      if ($plugin_type_default) {
        // TODO Here we should dynamically load fields from selected GroupContent bundles.
        if (strpos($plugin_type_default, 'group_membership') !== FALSE) {
          $group_roles = $this->groupRole->loadMultiple();
          $options = [];

          foreach ($group_roles as $group_role) {
            $options[$group_role->id()] = ucfirst($group_role->getGroupTypeId()) . ': ' . $group_role->label();
          }

          $element['group_roles'] = [
            '#type' => 'radios',
            '#title' => $this->t('Group type'),
            '#options' => $options,
            '#default_value' => $this->getSetting('group_roles'),
            '#required' => TRUE,
          ];
        }
      }
    }

    $element['#prefix'] = '<div id="edit-plugin-type-wrapper">';
    $element['#suffix'] = '</div>';
    return $element;
  }

  public function updatePluginType($form, FormStateInterface $form_state) {
    return $form['settings'];
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
    $parent_entity = $this->getParent()->getParent()->getValue();
    $value = $this->getValue();
    if ($update == FALSE) {
      if ($parent_entity->getRevisionId()) {
        $parent_entity->setNewRevision(FALSE);
      }
      // TODO Need user id here.
      $parent_entity->save();
    }
    elseif (!empty($value['from_widget'])) {
      $value = $this->getValue();
      $value_gid = $value['entity_gids'];

      $this->syncGroupContents($this->entityGidsValues, $value_gid, $parent_entity);
    }
  }

  /**
   * @param $old
   * @param $new
   * @param $parent_entity
   * @internal param $entity_id
   */
  private function syncGroupContents($old, $new, $parent_entity) {
    $properties = $this->buildGroupContentProperties($parent_entity);

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
        GroupContent::create($properties)->save();
      }
    }
  }
}
