<?php

namespace Drupal\migrate_social_autopost_implementation\Plugin\migrate\process;

use Drupal\Component\Utility\Unicode;
use Drupal\file\Entity\File;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Convert file id to url.
 *
 * @MigrateProcessPlugin(
 *   id = "fid_2_url"
 * )
 */
class Fid2Url extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $file = File::load($value);
    if (!empty($file)) {
      $value = file_create_url($file->getFileUri());

      if (Unicode::substr($value, 0, 2) == '//') {
        $value = 'https:' . $value;
      }

      return $value;
    }
    return $value;
  }

}
