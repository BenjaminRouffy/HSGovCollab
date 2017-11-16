<?php

namespace Drupal\events\Plugin\Field\FieldType;

use Drupal\color_field\Plugin\Field\FieldType\ColorFieldType;

/**
 * Plugin implementation of the 'event_color' field type.
 *
 * @FieldType(
 *   id = "event_color",
 *   label = @Translation("Event Color"),
 *   description = @Translation("Create and store event color value."),
 *   default_widget = "color_field_widget_default",
 *   default_formatter = "color_field_formatter_text"
 * )
 */
class EventColorItem extends ColorFieldType {

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
      /** @var \Drupal\node\Entity\Node $entity */
      $entity = $this->getEntity();
      if (!$entity->isNew()) {
        // Some custom code that retrieves the event color.
        if ($group = group_customization_get_node_region($entity)) {
          $value = group_customization_get_region_color($group);
          $this->setValue($value);
        }
      }
      $this->isCalculated = TRUE;
    }
  }

}
