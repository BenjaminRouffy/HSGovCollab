<?php

namespace Drupal\social_block\SocialNetwork;

// Core components.
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
// Custom components.
use Drupal\social_block\Annotation\SocialNetworkContentFetcher;

/**
 * Class ContentFetcherPluginManager.
 */
class ContentFetcherPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/SocialNetwork/ContentFetcher', $namespaces, $module_handler, ContentFetcherBase::class, SocialNetworkContentFetcher::class);

    $this->alterInfo('social_network_content_fetchers');
    $this->setCacheBackend($cache_backend, $this->alterHook);
  }

}
