<?php

namespace Drupal\sdk_linkedin\Sdk;

// SDK API components.
use Drupal\sdk\Api\Deriver\BaseDeriver;
// LinkedIn SDK.
use Happyr\LinkedIn\LinkedIn;

/**
 * Class LinkedInDeriver.
 */
class LinkedInDeriver extends BaseDeriver {

  /**
   * SDK instance.
   *
   * @var LinkedIn
   */
  private $instance;

  /**
   * {@inheritdoc}
   */
  protected function getInstance() {
    if (NULL === $this->instance) {
      $this->instance = new LinkedIn(
        $this->entity->settings['client_id'],
        $this->entity->settings['client_secret']
      );
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
    return $this->getInstance()->getLoginUrl([
      'scope' => $this->entity->settings['scope'],
      'redirect_uri' => $this->entity->getCallbackUrl(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function loginCallback() {
    try {
      $token = $this->getInstance()->getAccessToken();
      $this->setToken($token, $token->getExpiresAt()->getTimestamp());
    }
    catch (\Exception $e) {
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
