<?php

namespace Drupal\migrate_social\Plugin\SocialNetwork;

use Drupal\group\Entity\GroupContentType;
use Drupal\migrate\Row;
use Drupal\plugin_type_example\SandwichBase;
use Drupal\migrate_social\SocialNetworkBase;
use Drupal\views\Views;

/**
 * Provides a group related content plugin.
 *
 * @SocialNetwork(
 *   id = "facebook",
 *   description = @Translation("Related content by group.")
 * )
 */
class Facebook extends SocialNetworkBase {

  /**
   * {@inheritdoc}
   */
  protected function nextSource() {
    // TODO get this data from mapping.
    $body = $this->instance->get(sprintf('/me/feed?fields=id,full_picture,link,message,created_time,message_tags,story,permalink_url&limit=10000'))->getDecodedBody();

    if (!empty($body['data'])) {
      $this->iterator = new \ArrayIterator($body['data']);
      return TRUE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $result = $this->instance->post('me/feed', [
      'message' => $row->getDestinationProperty('message') . '1'
    ])->getDecodedBody();

    if (!empty($result)) {
      return [$result['id']];
    }
  }
}
