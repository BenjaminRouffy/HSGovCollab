<?php

namespace Drupal\social_block\Plugin\SocialNetwork\ContentFetcher;

use Drupal\Component\Serialization\Json;
use Drupal\social_block\SocialNetwork\ContentItem;
use Drupal\social_block\SocialNetwork\ContentFetcherBase;
use MetzWeb\Instagram\Instagram as SocialNetworkSdk;

/**
 * Class Instagram.
 *
 * @SocialNetworkContentFetcher("instagram")
 *
 * @property SocialNetworkSdk $instance
 */
class Instagram extends ContentFetcherBase {

  /**
   * {@inheritdoc}
   */
  const SOCIAL_NETWORK_URL = 'https://www.instagram.com';
  /**
   * {@inheritdoc}
   */
  const SDK_CLASS_NAMESPACE = SocialNetworkSdk::class;

  /**
   * {@inheritdoc}
   */
  public function fetch() {
    $query = $this->instance->searchUser($this->getAccount(), 1);

    if (!empty($query->data)) {
      $query = $this->instance->getUserMedia(reset($query->data)->id, $this->getNumberOfPostsPerNetwork());

      if (!empty($query->data)) {
        // Easy way to convert multidimensional structures to array.
        return Json::decode(Json::encode($query->data));
      }
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function processItem(ContentItem $item, array $source) {
    // Try to use the biggest image.
    foreach (['standard_resolution', 'low_resolution', 'thumbnail'] as $property) {
      if (isset($source['images'][$property]['url'])) {
        $item->setImage($source['images'][$property]['url']);
        break;
      }
    }

    $item->setText(static::rectify('@', '/', static::rectify('#', '/explore/tags', static::linkify($source['caption']['text']))));
    $item->setLink($source['link']);
    $item->setTimestamp($source['created_time']);
  }

}
