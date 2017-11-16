<?php

namespace Drupal\computed_group_membership\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\AllowedTagsXssTrait;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\OptGroup;

/**
 * Plugin implementation of the 'list_computed_group_membership' formatter.
 *
 * @FieldFormatter(
 *   id = "list_computed_group_membership",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "computed_group_membership",
 *   }
 * )
 */
class ListComputedGroupMembership extends FormatterBase {

  use AllowedTagsXssTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // Only collect allowed options if there are actually items to display.
    if ($items->count()) {
      foreach ($items as $delta => $item) {
        foreach($item->values as $key => $role) {
          $elements[$delta + $key] = [
            '#markup' => $role,
          ];
        }
      }
    }

    return $elements;
  }

}
