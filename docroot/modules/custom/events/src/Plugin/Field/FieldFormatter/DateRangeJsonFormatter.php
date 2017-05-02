<?php

namespace Drupal\events\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime_range\DateTimeRangeTrait;
use Drupal\datetime_range\Plugin\Field\FieldFormatter\DateRangeDefaultFormatter;
use Drupal\events\TimeRangeTrait;
use Drupal\p4h_views_plugins\Plugin\ComputedDate\Events;

/**
 * Plugin implementation of the 'Multiple ranges JSON' formatter for 'daterange' fields.
 *
 * This formatter renders the data range using <time> elements, with
 * configurable date formats (from the list of configured formats) and a
 * separator.
 *
 * @FieldFormatter(
 *   id = "daterange_multiple_json",
 *   label = @Translation("Multiple ranges JSON"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DateRangeJsonFormatter extends DateRangeDefaultFormatter {

  use DateTimeRangeTrait;
  use TimeRangeTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $separator = $this->getSetting('separator');

    // Get event period.
    $period = Events::getEventPeriod($items);

    if (!empty($period)) {
      $elements[0]['start_date'] = $this->buildDateWithIsoAttribute($period['start_date']);
      $elements[0]['separator'] = ['#plain_text' => ' ' . $separator . ' '];
      $elements[0]['end_date'] = $this->buildDateWithIsoAttribute($period['end_date']);
    }

    return $elements;
  }
}
