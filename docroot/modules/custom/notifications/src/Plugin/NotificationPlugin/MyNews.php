<?php

namespace Drupal\notifications\Plugin\NotificationPlugin;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\notifications\Plugin\NotificationPluginBase;

/**
 * @NotificationPlugin(
 *   id = "my_news",
 *   label = @Translation("My News"),
 *   selector = ".news"
 * )
 */
class MyNews extends NotificationPluginBase {

  /**
   * @deprecated
   */
  public function blockViewAlter(array &$build, BlockPluginInterface $block) {

    $query = \Drupal::database()->select('notifications_mapping', 'nm');
    $query->fields('nm', ['uid', 'type', 'notification']);
    $query->condition('nm.notification', 1);
    $query->condition('nm.uid', $this->currentUser->id());

    $results = $query->execute()->fetchAllAssoc('uid');

    if (count($results)) {
      $build['#attached']['drupalSettings']['notifications']['news'] = [
        'selector' => '.news',
      ];
    }
  }

}
