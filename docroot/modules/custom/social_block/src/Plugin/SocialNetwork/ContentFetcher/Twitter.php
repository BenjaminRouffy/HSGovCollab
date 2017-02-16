<?php

namespace Drupal\social_block\Plugin\SocialNetwork\ContentFetcher;

use Drupal\social_block\SocialNetwork\ContentItem;
use Drupal\social_block\SocialNetwork\ContentFetcherBase;
use Abraham\TwitterOAuth\TwitterOAuth as SocialNetworkSdk;

/**
 * Class Twitter.
 *
 * @SocialNetworkContentFetcher("twitter")
 *
 * @property SocialNetworkSdk $instance
 */
class Twitter extends ContentFetcherBase {

  /**
   * {@inheritdoc}
   */
  const SOCIAL_NETWORK_URL = 'https://twitter.com';
  /**
   * {@inheritdoc}
   */
  const SDK_CLASS_NAMESPACE = SocialNetworkSdk::class;

  /**
   * {@inheritdoc}
   */
  public function fetch() {
    $this->instance->setDecodeJsonAsArray(TRUE);

    return $this->instance->get('statuses/user_timeline', [
      'screen_name' => $this->getAccount(),
      'count' => $this->getNumberOfPostsPerNetwork(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function processItem(ContentItem $item, array $source) {
    $replace = [];

    foreach ([
      'hashtags' => ['#', 'text', '/hashtag/'],
      'user_mentions' => ['@', 'screen_name', '/'],
    ] as $entity => list($symbol, $property, $path)) {
      if (!empty($source['entities'][$entity])) {
        foreach ($source['entities'][$entity] as $element) {
          $find = $symbol . $element[$property];
          $replace[$find] = static::externalLink(static::externalUrl($path . $element[$property]), $find);
        }
      }
    }

    if (!empty($source['entities']['urls'])) {
      foreach ($source['entities']['urls'] as $element) {
        $replace[$element['url']] = static::externalLink($element['expanded_url'], $element['url']);
      }
    }

    if (!empty($source['entities']['media'])) {
      // Select first photo from the list of medias.
      foreach ($source['entities']['media'] as $element) {
        if ('photo' === $element['type']) {
          // When image attached to a tweet, then API will return
          // shortened link on that image inside of a tweet text.
          // Let's remove it since we are process images separately.
          $replace[$element['url']] = '';
          // Use absolute path to an image.
          $item->setImage($element['media_url_https']);
          break;
        }
      }
    }

    $item->setText(strtr($source['text'], $replace));
    $item->setLink(static::externalUrl("/{$source['user']['screen_name']}/status/{$source['id']}"));
    $item->setTimestamp($source['created_at']);
  }

}
