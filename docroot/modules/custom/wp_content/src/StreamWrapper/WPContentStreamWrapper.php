<?php

namespace Drupal\wp_content\StreamWrapper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\StreamWrapper\LocalStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;

/**
 * Class WPContentStreamWrapper.
 *
 * @package Drupal\wp_content
 */
class WPContentStreamWrapper extends LocalStream {

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::READ_VISIBLE;
  }

  /**
   * Returns the name of the stream wrapper for use in the UI.
   *
   * @return string
   *   The stream wrapper name.
   */
  public function getName() {
    return t('WP Content');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('WP Content old files.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    return 'wp-content/uploads';
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $path = str_replace('\\', '/', $this->getTarget());
    return $this->baseUrl() . '/' . UrlHelper::encodePath($path);
  }

  /**
   *
   */
  public function baseUrl() {
    return $GLOBALS['base_url'] . '/' . $this->getDirectoryPath();
  }

}
