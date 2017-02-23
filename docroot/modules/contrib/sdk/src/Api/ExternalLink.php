<?php

namespace Drupal\sdk\Api;

// Core components.
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\GeneratedLink;

/**
 * Trait ExternalLink.
 */
trait ExternalLink {

  /**
   * Create an external link.
   *
   * @param string $url
   *   Link URL.
   * @param string|null $text
   *   Link text. URL will be used if not specified.
   *
   * @return GeneratedLink
   *   Generated link.
   */
  public static function externalLink($url, $text = NULL) {
    if (NULL === $text) {
      $text = $url;
    }

    return (new Link($text, Url::fromUri($url, ['attributes' => ['target' => '_blank']])))->toString();
  }

}
