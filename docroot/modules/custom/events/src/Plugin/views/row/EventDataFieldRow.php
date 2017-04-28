<?php

namespace Drupal\events\Plugin\views\row;

use Drupal\rest\Plugin\views\row\DataFieldRow;
use Drupal\p4h_views_plugins\Plugin\ComputedDate\Events;

/**
 * Custom Plugin which displays Event fields as raw data.
 *
 * @ingroup views_row_plugins
 *
 * @ViewsRow(
 *   id = "event_data_field",
 *   title = @Translation("Custom Event Fields"),
 *   help = @Translation("Use Event fields as row data."),
 *   display_types = {"data"}
 * )
 */
class EventDataFieldRow extends DataFieldRow {
  /**
   * {@inheritdoc}
   */
  public function render($row) {
    $output = [];

    foreach ($this->view->field as $id => $field) {
      // If the raw output option has been set, just get the raw value.
      if (!empty($this->rawOutputOptions[$id])) {
        $value = $field->getValue($row);
      }
      // Otherwise, pass this through the field advancedRender() method.
      else {
        $value = $field->advancedRender($row);
      }

      // Check if field is Event date field.
      if ('field_date' == $id) {
        $period = Events::getEventPeriod($row->_entity->field_date);
        $value = [
          'start' => $period['start_date']->getTimestamp(),
          'end' => $period['end_date']->getTimestamp(),
        ];
      }
      // Omit excluded fields from the rendered output.
      if (empty($field->options['exclude'])) {
        $output[$this->getFieldKeyAlias($id)] = $value;
      }
    }

    return $output;
  }
}
