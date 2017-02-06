<?php

namespace Drupal\options\Plugin\Field\FieldType;

use Drupal\Core\Field\AllowedTagsXssTrait;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;

/**
 * List rel content field type.
 *
 * @FieldType(
 *   id = "list_rel_content",
 *   label = @Translation("List rel content"),
 *   default_widget = "list_rel_content_select",
 *   default_formatter = "list_rel_content_id",
 * )
 */
abstract class ListRelContent extends FieldItemBase  {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'list_rel_content' => [],
    ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $list_rel_content = $this->getSetting('list_rel_content');

    $element['list_rel_content'] = array(
      '#type' => 'textarea',
      '#title' => t('Allowed values list'),
      '#rows' => 10,
    );


    return $element;
  }

}
