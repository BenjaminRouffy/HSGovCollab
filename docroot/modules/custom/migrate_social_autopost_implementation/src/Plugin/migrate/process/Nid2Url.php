<?php

namespace Drupal\migrate_social_autopost_implementation\Plugin\migrate\process;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Convert node id to url.
 *
 * @MigrateProcessPlugin(
 *   id = "nid_2_url"
 * )
 */
class Nid2Url extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return  Url::fromRoute('entity.node.canonical', ['node' => $value], ['absolute' => TRUE])->toString();
  }

}
