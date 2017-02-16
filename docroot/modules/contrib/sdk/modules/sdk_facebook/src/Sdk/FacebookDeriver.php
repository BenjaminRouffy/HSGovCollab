<?php

namespace Drupal\sdk_facebook\Sdk;

// SDK API components.
use Drupal\sdk\Api\Deriver\BaseDeriver;
// Facebook SDK.
use Facebook\Facebook;

/**
 * Class FacebookDeriver.
 */
class FacebookDeriver extends BaseDeriver {

  /**
   * SDK instance.
   *
   * @var Facebook
   */
  private $instance;

  /**
   * {@inheritdoc}
   */
  protected function getInstance() {
    if (NULL === $this->instance) {
      $this->instance = new Facebook([
        'app_id' => $this->entity->settings['app_id'],
        'app_secret' => $this->entity->settings['app_secret'],
        'default_graph_version' => 'v' . (float) $this->entity->settings['api_version'],
      ]);
    }

    return $this->instance;
  }

  /**
   * {@inheritdoc}
   */
  public function derive() {
    $instance = $this->getInstance();
    $token = $this->getToken();

    if (NULL !== $token) {
      $instance->setDefaultAccessToken($token);
    }

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function loginUrl() {
    return $this->getInstance()->getRedirectLoginHelper()->getLoginUrl(
      $this->entity->getCallbackUrl(),
      $this->entity->settings['scope']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function loginCallback() {
    $token = $this->getInstance()->getRedirectLoginHelper()->getAccessToken();

    if (NULL !== $token) {
      $this->setToken($token, $token->getExpiresAt()->getTimestamp());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenExpiration() {
    $token = $this->getToken();

    return NULL === $token ? NULL : $token->getExpiresAt();
  }

}
