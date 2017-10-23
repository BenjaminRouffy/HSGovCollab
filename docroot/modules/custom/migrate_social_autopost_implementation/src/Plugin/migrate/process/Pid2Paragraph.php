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
 *   id = "pid_2_paragraph"
 * )
 */
class Pid2Paragraph extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return Paragraph::load($value);
  }

}
