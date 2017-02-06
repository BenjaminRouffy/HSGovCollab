<?php

namespace Drupal\pluginreference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'plugin_reference_select' widget.
 *
 * @FieldWidget(
 *   id = "list_rel_content_select",
 *   label = @Translation("Select list"),
 *   field_types = {
 *     "list_rel_content"
 *   }
 * )
 */
class ListRelContentSelectWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $options = [];

    if ($manager = \Drupal::getContainer()->has('plugin.manager.' . $this->getFieldSetting('target_type'))) {
      foreach (\Drupal::getContainer()
                 ->get('plugin.manager.' . $this->getFieldSetting('target_type'))
                 ->getDefinitions() as $plugin_type_id => $plugin_definition) {
        $options[\Drupal::moduleHandler()->getName($plugin_definition['provider'])][$plugin_type_id] = isset($plugin_definition['label']) ? $plugin_definition['label'] : $plugin_type_id;
      }
    }

    $element['value'] = $element + [
        '#type' => 'select',
        '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
        '#options' => $options,
      ];

    return $element;
  }

}
