<?php

namespace Drupal\rel_content;
use Drupal\rel_content\Plugin\views\filter\RelatedContentFilter;

/**
 * An interface for all RelatedContent type plugins.
 */
interface RelatedContentInterface {

  /**
   * Provide a description of the plugin.
   *
   * @return string
   *   A string description of the plugin.
   */
  public function description();

  /**
   * Get related content options for selected plugin.
   *
   * @return array
   *   A string description of the plugin.
   */
  public function getOptions();

  /**
   * Alter view. TODO change
   * @param RelatedContentFilter $date
   */
  public function viewsAlteration(&$date);
}
