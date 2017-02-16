<?php

/**
 * @file
 * Social Block API.
 */

/**
 * Alter definitions of content fetchers.
 *
 * @param array[] $definitions
 *   An associative array where keys - are plugin IDs and
 *   value - are structured arrays with the following format:
 *   - id: an ID of plugin, specified by annotation;
 *   - class: full path to class which is implements the plugin;
 *   - provider: name of a module which is provides the plugin.
 *
 * @see \Drupal\social_block\SocialNetwork\ContentFetcherPluginManager::__construct()
 */
function hook_social_network_content_fetchers_alter(array &$definitions) {

}

/**
 * Alter content item from one of social networks.
 *
 * @param \Drupal\social_block\SocialNetwork\ContentItem $item
 *   Formed content item.
 * @param array $source
 *   Source data from which content item was formed.
 * @param string $network
 *   Machine name of social network.
 *
 * @see \Drupal\social_block\SocialNetwork\ContentFetcherBase::getItems()
 */
function hook_social_network_content_item_alter(\Drupal\social_block\SocialNetwork\ContentItem $item, array $source, $network) {

}
