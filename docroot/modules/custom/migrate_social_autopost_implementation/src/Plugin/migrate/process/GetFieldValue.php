<?php

namespace Drupal\migrate_social_autopost_implementation\Plugin\migrate\process;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Convert paragraph id to paragraph.
 *
 * @MigrateProcessPlugin(
 *   id = "get_field_value"
 * )
 */
class GetFieldValue extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!empty($this->configuration['field_name'])) {
      if ($field_value = $value->{$this->configuration['field_name']}) {
        return $field_value->getValue();
      }
    }
  }

}
