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
 * Iterate until text comes.
 *
 * @MigrateProcessPlugin(
 *   id = "get_first_text_from_paragraph",
 *   handle_multiples = TRUE
 * )
 */
class GetFirstTextFromParagraph extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    foreach ($value as $pid) {
      $paragraph = Paragraph::load($pid['target_id']);

      if (!empty($paragraph)) {
        switch ($paragraph->getType()) {
          case 'content_text':
            return $paragraph;
            break;

        }

      }
    }

    return NULL;
  }

}
