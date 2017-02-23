<?php

namespace Drupal\sdk_twitter\Sdk;

// SDK API components.
use Drupal\sdk\Api\Deriver\BaseDeriver;
// Twitter SDK.
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class TwitterDeriver.
 */
class TwitterDeriver extends BaseDeriver {

  /**
   * SDK instance.
   *
   * @var TwitterOAuth
   */
  private $instance;

  /**
   * {@inheritdoc}
   */
  protected function getInstance() {
    if (NULL === $this->instance) {
      $this->instance = new TwitterOAuth(
        $this->entity->settings['consumer_key'],
        $this->entity->settings['consumer_secret'],
        $this->entity->settings['access_key'],
        $this->entity->settings['access_secret']
      );
    }

    return $this->instance;
  }

}
