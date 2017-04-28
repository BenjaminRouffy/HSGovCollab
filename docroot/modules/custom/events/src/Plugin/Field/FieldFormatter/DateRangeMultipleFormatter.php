<?php

namespace Drupal\events\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime_range\DateTimeRangeTrait;
use Drupal\datetime_range\Plugin\Field\FieldFormatter\DateRangeDefaultFormatter;
use Drupal\events\TimeRangeTrait;
use Drupal\p4h_views_plugins\Plugin\ComputedDate\Events;

/**
 * Plugin implementation of the 'Multiple ranges' formatter for 'daterange' fields.
 *
 * This formatter renders the data range using <time> elements, with
 * configurable date formats (from the list of configured formats) and a
 * separator.
 *
 * @FieldFormatter(
 *   id = "daterange_multiple",
 *   label = @Translation("Multiple ranges"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DateRangeMultipleFormatter extends DateRangeDefaultFormatter {

  use DateTimeRangeTrait;
  use TimeRangeTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $separator = $this->getSetting('separator');

    foreach ($items as $delta => $item) {
      if (!empty($item->start_date) && !empty($item->end_date)) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
        $start_date = $item->start_date;
        /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
        $end_date = $item->end_date;

        if ($this->formatDate($start_date) !== $this->formatDate($end_date)) {
          $elements[$delta] = [
            'start_date' => $this->buildDateWithIsoAttribute($start_date),
            'separator' => ['#plain_text' => ' ' . $separator . ' '],
            'end_date' => $this->buildDateWithIsoAttribute($end_date),
            'start_time' => $this->buildTimeWithIsoAttribute($start_date),
            'time_separator' => ['#plain_text' => ' ' . $separator . ' '],
            'end_time' => $this->buildTimeWithIsoAttribute($end_date),
            '#weight' => $start_date->getTimestamp(),
          ];
        }
        else {
          $elements[$delta]['start_date'] = $this->buildDateWithIsoAttribute($start_date);
          $elements[$delta]['#weight'] = $start_date->getTimestamp();
          $elements[$delta]['start_time'] = $this->buildTimeWithIsoAttribute($start_date);
          $elements[$delta]['separator'] = ['#plain_text' => ' ' . $separator . ' '];
          $elements[$delta]['end_time'] = $this->buildTimeWithIsoAttribute($end_date);
          if (!empty($item->_attributes)) {
            $elements[$delta]['#attributes'] += $item->_attributes;
            // Unset field item attributes since they have been included in the
            // formatter output and should not be rendered in the field template.
            unset($item->_attributes);
          }
        }
      }
    }

    $sorted_ranges = array();
    foreach ($elements as $key => $row) {
      $sorted_ranges[$key] = $row['#weight'];
    }
    array_multisort($sorted_ranges, SORT_DESC, $elements);
    // Get event period.
    $period = Events::getEventPeriod($items);
    if (!empty($period)) {
      $elements['#period']['start_date'] = $this->buildDateWithIsoAttribute($period['start_date']);
      if ($this->formatDate($period['start_date']) != $this->formatDate($period['end_date'])) {
        $elements['#period']['separator'] = ['#plain_text' => ' ' . $separator . ' '];
        $elements['#period']['end_date'] = $this->buildDateWithIsoAttribute($period['end_date']);
      }
    }

    return $elements;
  }
}
