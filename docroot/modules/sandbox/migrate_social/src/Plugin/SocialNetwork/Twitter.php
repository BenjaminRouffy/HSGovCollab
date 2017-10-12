<?php

namespace Drupal\migrate_social\Plugin\SocialNetwork;

use Drupal\group\Entity\GroupContentType;
use Drupal\migrate\Row;
use Drupal\plugin_type_example\SandwichBase;
use Drupal\migrate_social\SocialNetworkBase;
use Drupal\views\Views;

/**
 * Provides a twitter migrate plugin
 *
 * @SocialNetwork(
 *   id = "twitter",
 *   description = @Translation("Twitter migrate plugin.")
 * )
 */
class Twitter extends SocialNetworkBase {

  /**
   * {@inheritdoc}
   */
  protected function nextSource() {
    $this->instance->setDecodeJsonAsArray(TRUE);

    $result =  $this->instance->get('statuses/user_timeline', [
      'tweet_mode' => 'extended',
      'count' => 100000,
    ]);

    if (!empty($result[0]['id'])) {
      $this->iterator = new \ArrayIterator($result);
      return TRUE;
    }

  }

  /**
   * Import/update one row to social network.
   */
  protected function import(Row $row, array $old_destination_id_values = []) {
    // TODO: Implement import() method.
  }
}
