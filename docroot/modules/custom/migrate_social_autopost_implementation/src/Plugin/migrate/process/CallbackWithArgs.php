<?php

namespace Drupal\migrate_social_autopost_implementation\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Passes the source value to a callback.
 * @MigrateProcessPlugin(
 *   id = "callback_with_args"
 * )
 */
class CallbackWithArgs extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_callable($this->configuration['callable'])) {
      if (empty($this->configuration['args'])) {
        $this->configuration['args'] = [];
      }
      $args = $this->configuration['args'];
      array_unshift($args, $value);

      $value = call_user_func_array($this->configuration['callable'], $args);
    }
    return $value;
  }

}
