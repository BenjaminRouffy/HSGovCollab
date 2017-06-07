<?php

namespace Drupal\migrate_wp\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Remove old url
 *
 * @MigrateProcessPlugin(
 *   id = "remove_old_url",
 *   handle_multiples = TRUE
 * )
 */
class RemoveOldUrl extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return str_replace('http://intranet.p4h-network.net', '', $value);
  }

}
