<?php

namespace Drupal\events\Plugin\Field\FieldFormatter;

use Drupal\datetime_range\Plugin\Field\FieldFormatter\DateRangeDefaultFormatter;

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

}
