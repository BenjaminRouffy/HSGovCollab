<?php

namespace Drupal\rel_content;

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
}
