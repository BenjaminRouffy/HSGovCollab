<?php

namespace Drupal\social_block\SocialNetwork;

// Core components.
use Drupal\Component\Plugin\PluginBase;
use Drupal\sdk\Api\ExternalLink;

/**
 * Class ContentFetcherBase.
 */
abstract class ContentFetcherBase extends PluginBase {

  use ExternalLink;

  /**
   * Base URL of a social network. Must be defined by child class.
   */
  const SOCIAL_NETWORK_URL = '';
  /**
   * Full namespace of SDK class which must be instantiated.
   */
  const SDK_CLASS_NAMESPACE = '';

  /**
   * Collection of fetched items from social network.
   *
   * @var ContentItem[]
   */
  private $items = [];
  /**
   * An instance of SDK of social network.
   *
   * @var object
   */
  protected $instance;

  /**
   * Execute REST query and fetch the data.
   *
   * @return array[]
   *   List of entities to process.
   */
  abstract public function fetch();

  /**
   * Forming a content item.
   *
   * @param ContentItem $item
   *   Content item to form.
   * @param array $source
   *   Raw data from source.
   */
  abstract protected function processItem(ContentItem $item, array $source);

  /**
   * Returns link on a profile page in social network.
   *
   * @return string
   *   Link to page.
   */
  public function getProfileLink() {
    return static::externalUrl('/' . $this->getAccount());
  }

  /**
   * {@inheritdoc}
   */
  final public function getItems() {
    if (empty($this->items)) {
      $module_handler = \Drupal::moduleHandler();
      $sdk_class_namespace = static::SDK_CLASS_NAMESPACE;
      // Instantiate SDK.
      $this->instance = sdk($this->pluginId);

      if ('' !== $sdk_class_namespace && !($this->instance instanceof $sdk_class_namespace)) {
        throw new \RuntimeException(sprintf(
          'The "%s" content fetcher expects SDK of "%s" type, "%s" used.',
          $this->pluginId,
          $sdk_class_namespace,
          get_class($this->instance)
        ));
      }

      foreach ($this->fetch() as $source) {
        $item = new ContentItem();
        $this->processItem($item, $source);
        // Re-save value into new variable because it will be passed
        // by reference and can be broken by some armass guys.
        $network = $this->pluginId;
        // Give other modules a chance to change the item.
        $module_handler->alter('social_network_content_item', $item, $source, $network);

        if (!$item->isInvalid()) {
          $item->setNetwork($this->pluginId);
          $this->items[] = $item;
        }
      }
    }

    return $this->items;
  }

  /**
   * Get username or ID of a unit in social network.
   *
   * @return string
   *   Username or ID of a unit.
   */
  final public function getAccount() {
    return $this->configuration['accounts'][$this->pluginId];
  }

  /**
   * Returns calculated number of posts to fetch from every social network.
   *
   * @return int
   *   Number of posts to fetch.
   */
  final public function getNumberOfPostsPerNetwork() {
    return ceil($this->configuration['count'] / count($this->configuration['networks']));
  }

  /**
   * Convert all absolute URLs into HTML links.
   *
   * @param string $string
   *   Original text.
   *
   * @return string
   *   Processed text.
   */
  protected static function linkify($string) {
    $replaces = [];

    preg_match_all('/[https?:\/\/]?[\da-z\.-]+\.[a-z\.]{2,6}[\/\w-]*\/?/i', $string, $matches);

    if (!empty($matches)) {
      foreach (reset($matches) as $url) {
        $uri_parts = parse_url($url);

        if (empty($uri_parts['scheme'])) {
          $url = 'https://' . $url;
        }

        $replaces[$url] = static::externalLink($url);
      }
    }

    return strtr($string, $replaces);
  }

  /**
   * Convert all hashtags/mentions into HTML links on listing pages.
   *
   * @param string $symbol
   *   Symbol to identify.
   * @param string $uri
   *   URI prefix of listing page. F.e.: "/hashtag/<TAG_WILL_BE_HERE>".
   * @param string $string
   *   Original text.
   *
   * @return string
   *   Processed text.
   */
  protected static function rectify($symbol, $uri, $string) {
    preg_match_all('/(?:' . $symbol . '\w+)/i', $string, $matches);

    if (empty($matches)) {
      return $string;
    }

    $replaces = [];
    $uri = static::externalUrl($uri);

    foreach (reset($matches) as $item) {
      $replaces[$item] = static::externalLink($uri . '/' . ltrim($item, $symbol), $item);
    }

    return strtr($string, $replaces);
  }

  /**
   * Create an URL of page in a social network.
   *
   * @param string $uri
   *   URI to append.
   *
   * @return string
   *   Absolute URL.
   */
  protected static function externalUrl($uri) {
    return rtrim(static::SOCIAL_NETWORK_URL, '/') . rtrim($uri, '/');
  }

}
