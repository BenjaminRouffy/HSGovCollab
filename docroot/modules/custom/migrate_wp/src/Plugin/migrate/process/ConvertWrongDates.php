<?php

namespace Drupal\migrate_wp\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Plugin to replace wrong dates.
 *
 * @MigrateProcessPlugin(
 *   id = "convert_wrong_dates",
 *   handle_multiples = TRUE
 * )
 */
class ConvertWrongDates extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $tokens = [
      // 3016 -> 2016
      '33035641200' => '1478732400',
    ];

    if (is_null($value)) {
      $value = '';
    }

    return str_replace(array_keys($tokens), $tokens, (string) $value);
  }

}
