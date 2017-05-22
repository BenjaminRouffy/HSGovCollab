<?php

/**
 * @file
 * Contains \Drupal\migrate_wp\Plugin\migrate\process\ParagraphsImport.
 */

namespace Drupal\migrate_wp\Plugin\migrate\process;

use Drupal\Core\Entity\EntityInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 *
 * @MigrateProcessPlugin(
 *   id = "paragraphs_import"
 * )
 */
class ParagraphsImport extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform( $value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property ) {
    if (!isset($this->configuration['paragraph_type'])) {
      throw new MigrateException('Specify a paragraph type.');
    }

    $paragraph = entity_load('paragraph', $value);
    if (!($paragraph instanceof EntityInterface)) {
      return NULL;
    }

    return $paragraph;

  }

}
