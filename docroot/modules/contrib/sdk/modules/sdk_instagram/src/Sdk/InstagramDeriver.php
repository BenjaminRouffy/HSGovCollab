<?php

namespace Drupal\sdk_instagram\Sdk;

// SDK API components.
use Drupal\sdk\Api\Deriver\BaseDeriver;
// Instagram SDK.
use MetzWeb\Instagram\Instagram;

/**
 * Class InstagramDeriver.
 */
class InstagramDeriver extends BaseDeriver {

  /**
   * SDK instance.
   *
   * @var Instagram
   */
  private $instance;

  /**
   * {@inheritdoc}
   */
  protected function getInstance() {
    if (NULL === $this->instance) {
      $this->instance = new Instagram([
        'apiKey' => $this->entity->settings['client_id'],
        'apiSecret' => $this->entity->settings['client_secret'],
        'apiCallback' => $this->entity->getCallbackUrl(),
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
      $instance->setAccessToken($token);
    }

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function loginUrl() {
    return $this->getInstance()->getLoginUrl($this->entity->settings['scope']);
  }

  /**
   * {@inheritdoc}
   */
  public function loginCallback() {
    if (isset($_GET['code'])) {
      $this->setToken($this->getInstance()->getOAuthToken($_GET['code']), strtotime('+ 365 days'));
    }
  }

}
