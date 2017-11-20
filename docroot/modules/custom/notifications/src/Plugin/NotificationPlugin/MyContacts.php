<?php

namespace Drupal\notifications\Plugin\NotificationPlugin;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\notifications\Plugin\NotificationPluginBase;

/**
 * @property  currentUser
 * @NotificationPlugin(
 *   id = "my_contacts",
 *   label = @Translation("My Contacts"),
 *   selector = ".contacts"
 * )
 */
class MyContacts extends NotificationPluginBase {

  /**
   * @param array $build
   * @param \Drupal\Core\Block\BlockPluginInterface $block
   */
  public function blockViewAlter(array &$build, BlockPluginInterface $block) {
    $query = \Drupal::entityQuery('relation');
    $query->condition('uid', $this->currentUser->id());
    $query->condition('relation_type', 'friend');
    $query->condition('field_relation_status', 'pending');
    $query->condition('changed', $this->get(), '>');
    $entity_ids = $query->execute();
    if (count($entity_ids)) {
      $this->setSettings($build, [
        'args' => http_build_query([
          'field_relation_status_value' => ['pending'],
        ]),
      ]);
    }
  }

}
