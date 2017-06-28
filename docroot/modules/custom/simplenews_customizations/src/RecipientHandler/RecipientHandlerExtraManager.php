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
   *
   * @param null $id
   *
   * @return array
   */
  public function getOptions($id = NULL) {
    $handlers = $this->getDefinitions();

    $allowed_values = [];
    foreach ($handlers as $handler => $settings) {
      if (is_null($id) || empty($settings['types']) || in_array($id, $settings['types'])) {
        $allowed_values[$handler] = Xss::filter($settings['title']);
      }
    }

    return $allowed_values;
  }

  /**
   *
   */
  public function getDefaultOptions($id) {
    $options = $this->getOptions($id);
    reset($options);
    return $options ? key($options) : FALSE;
  }

}
