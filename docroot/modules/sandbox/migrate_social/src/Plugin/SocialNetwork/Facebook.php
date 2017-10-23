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
  protected function getSocialRows() {
    // TODO get this data from mapping.
    $body = $this->instance->get(sprintf('/me/feed?fields=id,full_picture,link,message,created_time,message_tags,story,permalink_url,application&limit=10000'))->getDecodedBody();

    return empty($body['data']) ? [] : $body['data'];
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $post = [
      'message' => $row->getDestinationProperty('message'),
    ];

    // FB supports add image only on creation.
    foreach ($row->getDestinationProperty('attached_media') as $delta => $picture) {
      $result = $this->instance->post('me/photos', [
        'url' => $picture['url'],
        'published' => FALSE,
      ])->getDecodedBody();

      if (!empty($result['id'])) {
        $post["attached_media[$delta]"] = ['media_fbid' => $result['id']];
      }
    }

    if (!empty( $old_destination_id_values[0])) {
      // Update current post /post-id.
      $result = $this->instance->post($old_destination_id_values[0], $post)->getDecodedBody();
      if ($result['success']) {
        return [$old_destination_id_values[0]];
      }
    }
    else {
      $result = $this->instance->post('me/feed', $post)->getDecodedBody();
      return [$result['id']];
    }
  }
}
