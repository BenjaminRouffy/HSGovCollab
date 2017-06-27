<?php

namespace Drupal\simplenews_customizations\RecipientHandler;

use Drupal\Component\Utility\Xss;
use Drupal\simplenews\RecipientHandler\RecipientHandlerManager;

/**
 * Provides an recipient handler plugin manager.
 *
 * @see \Drupal\simplenews_customizations\RecipientHandler\Annotations\RecipientHandler
 * @see \Drupal\simplenews_customizations\RecipientHandler\RecipientHandlerInterface
 * @see plugin_api
 */
class RecipientHandlerExtraManager extends RecipientHandlerManager {

  /**
   * Returns the array of recipient handler labels.
   *
   * @todo documentation
   */
  public function getOptions($id) {
    $handlers = $this->getDefinitions();

    $allowed_values = [];
    foreach ($handlers as $handler => $settings) {
      if (in_array($id, $settings['types'])) {
        $allowed_values[$handler] = Xss::filter($settings['title']);
      }
    }

    return $allowed_values;
  }

}
