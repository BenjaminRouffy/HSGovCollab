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
  protected function getSocialRows() {
    $this->instance->setDecodeJsonAsArray(TRUE);

    $result = $this->instance->get('statuses/user_timeline', [
      'tweet_mode' => 'extended',
      'count' => 100000,
    ]);

    return empty($result[0]['id']) ? [] : $result;

  }

  /**
   * Import/update one row to social network.
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    if (!empty($old_destination_id_values[0])) {
      // Do nothing cause twitter doesn't allow editing.
      return [$old_destination_id_values[0]];
    }
    else {
      $result = $this->instance->post('statuses/update', [
        'status' => $row->getDestinationProperty('status'),
      ]);

      if (empty($result->errors)) {
        return [$result->id];
      }
    }
  }
}
