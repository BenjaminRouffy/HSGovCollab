<?php

namespace Drupal\social_block\Plugin\SocialNetwork\ContentFetcher;

use Drupal\social_block\SocialNetwork\ContentItem;
use Drupal\social_block\SocialNetwork\ContentFetcherBase;
use Happyr\LinkedIn\LinkedIn as SocialNetworkSdk;

/**
 * Class LinkedIn.
 *
 * @SocialNetworkContentFetcher("linkedin")
 *
 * @property SocialNetworkSdk $instance
 */
class LinkedIn extends ContentFetcherBase {

  /**
   * {@inheritdoc}
   */
  const SOCIAL_NETWORK_URL = 'https://www.linkedin.com';
  /**
   * {@inheritdoc}
   */
  const SDK_CLASS_NAMESPACE = SocialNetworkSdk::class;

  /**
   * {@inheritdoc}
   */
  public function fetch() {
    $query = $this->instance->setResponseDataType('array')->get(sprintf('v1/companies/%s/updates', $this->getAccount()), [
      'query' => [
        'count' => $this->getNumberOfPostsPerNetwork(),
      ],
    ]);

    return empty($query['values']) ? [] : $query['values'];
  }

  /**
   * {@inheritdoc}
   */
  public function processItem(ContentItem $item, array $source) {
    $entry = $source['updateContent']['companyStatusUpdate']['share'];
    // The value inside of "updateKey" contains something like
    // "UPDATE-c10922499-6179936490957598720". Last part of such
    // keys - is always an unique ID of post.
    $key_parts = explode('-', $source['updateKey']);

    if (!empty($entry['content']['submittedImageUrl'])) {
      $item->setImage($entry['content']['submittedImageUrl']);
    }

    // LinkedIn does not provide something like "entities" property,
    // which Twitter gives, and we need convert all URLs to the HTML
    // links in text using regular expression.
    $item->setText(static::linkify($entry['comment']));
    // Forming the link on posts was spied here. Check the link below.
    // @link http://stackoverflow.com/a/27380234.
    $item->setLink(static::externalUrl('/hp/updates?topic=' . end($key_parts)));
    $item->setTimestamp($source['timestamp']);
  }

  /**
   * {@inheritdoc}
   */
  public function getProfileLink() {
    return static::externalUrl('/company/' . $this->getAccount());
  }

}
