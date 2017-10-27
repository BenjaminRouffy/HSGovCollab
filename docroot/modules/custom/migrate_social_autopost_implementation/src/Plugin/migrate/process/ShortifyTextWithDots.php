<?php

namespace Drupal\migrate_social_autopost_implementation\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Shortify text with dots if needed.
 * @MigrateProcessPlugin(
 *   id = "shortify_text_with_dots"
 * )
 */
class ShortifyTextWithDots extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!empty($this->configuration['length']) && (strlen($value) > $this->configuration['length'])) {
      $value = substr($value, 0, $this->configuration['length']) . '...';
    }

    return $value;
  }

}
