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
 * Get first image fid from paragraph image or slider.
 *
 * @MigrateProcessPlugin(
 *   id = "get_first_image_fid"
 * )
 */
class GetFirstImageFid extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $paragraph = Paragraph::load($value);

    switch ($paragraph->getType()) {
      case 'slider':
        $field_slider = $paragraph->get('field_slider')->getValue();

        if (!empty($field_slider[0]['target_id'])) {
          $slider_item = Paragraph::load($field_slider[0]['target_id']);
          $field_content_image = $slider_item->get('field_content_image')->getValue();
        }
        break;

      case 'content_image':
        $field_content_image = $paragraph->get('field_content_image')->getValue();
        break;
    }

    if (!empty($field_content_image[0]['target_id'])) {
      return $field_content_image[0]['target_id'];
    }

    return NULL;
  }

}
