<?php

namespace Drupal\group_content_field\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'plugin_reference_id' formatter.
 *
 * @FieldFormatter(
 *   id = "group_content_list",
 *   label = @Translation("Group content list."),
 *   field_types = {
 *     "group_content_item"
 *   }
 * )
 */
class GroupContentListFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode):array {
    $elements = [];

    foreach ($items as $delta => $item) {
      $value = $item->getValue();
      $elements[$delta] = ['#markup' => 'test me please'];
    }

    return $elements;
  }

}
