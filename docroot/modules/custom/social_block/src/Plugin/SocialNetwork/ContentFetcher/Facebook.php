<?php

namespace Drupal\social_block\Plugin\SocialNetwork\ContentFetcher;

use Drupal\social_block\SocialNetwork\ContentItem;
use Drupal\social_block\SocialNetwork\ContentFetcherBase;
use Facebook\Facebook as SocialNetworkSdk;

/**
 * Class Facebook.
 *
 * @SocialNetworkContentFetcher("facebook")
 *
 * @property SocialNetworkSdk $instance
 */
class Facebook extends ContentFetcherBase {

  /**
   * {@inheritdoc}
   */
  const SOCIAL_NETWORK_URL = 'https://www.facebook.com';
  /**
   * {@inheritdoc}
   */
  const SDK_CLASS_NAMESPACE = SocialNetworkSdk::class;

  /**
   * {@inheritdoc}
   */
  public function fetch() {
    $body = $this->instance->get(sprintf('/me/feed?fields=id,full_picture,link,message,created_time,message_tags&limit=10000'))->getDecodedBody();

    return empty($body['data']) ? [] : $body['data'];
  }

  /**
   * {@inheritdoc}
   */
  public function processItem(ContentItem $item, array $source) {
    if (!empty($source['full_picture'])) {
      $item->setImage($source['full_picture']);
    }

    if (!empty($source['message'])) {
      // Replace all raw URL and hashtags by HTML links. We do it here because
      // search performs using regex inside of a raw text without any markup.
      // Further processing will generate a markup and regexps will break it.
      $source['message'] = static::rectify('#', '/hashtag', static::linkify($source['message']));

      if (!empty($source['message_tags'])) {
        $replaces = [];

        foreach ($source['message_tags'] as $message_tag) {
          foreach ($message_tag as $value) {
            $replaces[$value['name']] = static::externalLink(static::externalUrl('/' . $value['id']), $value['name']);
          }
        }

        $source['message'] = strtr($source['message'], $replaces);
      }

      $item->setText($source['message']);
    }

    $item->setLink(empty($source['link']) ? static::externalUrl(vsprintf('/%s/posts/%s', explode('_', $source['id']))) : $source['link']);
    $item->setTimestamp($source['created_time']);
  }

}
