<?php

namespace Drupal\country\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityStorageBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group_content_field\Plugin\Field\FieldWidget\GroupSelectWidget;
use Drupal\rel_content\RelatedContentInterface;

/**
 * Plugin implementation of the 'plugin_reference_select' widget.
 *
 * @FieldWidget(
 *   id = "group_country_select",
 *   label = @Translation("Group country select"),
 *   field_types = {
 *     "group_content_item"
 *   }
 * )
 */
class GroupCountrySelectWidget extends GroupSelectWidget {

  /**
   * @inheritdoc
   */
  protected function getGroups($group_type) {
    $options = [];

    foreach(\Drupal::entityTypeManager()->getStorage('group')->loadByProperties(['type' => $group_type]) as $key => $group) {
      $options[$key] = $group->label();
    }

    return $options;
  }

}
