<?php

namespace Drupal\sdk\Api\Deriver;

// Core components.
use Drupal\Core\Url;
use Drupal\Core\KeyValueStore\DatabaseStorageExpirable;
use Drupal\Core\KeyValueStore\KeyValueDatabaseExpirableFactory;
use Drupal\Core\Routing\TrustedRedirectResponse;
// SDK API components.
use Drupal\sdk\Api\Api;

/**
 * Class BaseDeriver.
 */
abstract class BaseDeriver extends Api {

  /**
   * Returns an instance of SDK.
   *
   * @return object
   *   SDK instance.
   */
  abstract protected function getInstance();

  /**
   * Derive an instance of SDK.
   *
   * @return object
   *   Derived instance of SDK.
   */
  public function derive() {
    return $this->getInstance();
  }

  /**
   * Return URL to redirect to for login.
   *
   * @return string
   *   URL to redirect to for login and token obtaining.
   */
  public function loginUrl() {
    if (!$this->isLoginCallbackOverridden()) {
      throw new \RuntimeException(sprintf('The "%s" method must be overridden by "%s" class', 'loginCallback', static::class));
    }

    return '';
  }

  /**
   * Process result of visiting the login URL.
   */
  public function loginCallback() {
  }

  /**
   * Check whether "loginCallback" method has been overridden.
   *
   * @return bool
   *   A state of check.
   */
  final public function isLoginCallbackOverridden() {
    return (new \ReflectionMethod($this, 'loginCallback'))->getDeclaringClass()->getName() !== self::class;
  }

  /**
   * Returns storage.
   *
   * @return DatabaseStorageExpirable
   *   Database storage for SDK purposes.
   */
  public static function storage() {
    $service = \Drupal::service('keyvalue.expirable.database');

    if ($service instanceof KeyValueDatabaseExpirableFactory) {
      $service->garbageCollection();
    }

    return $service->get('sdk_storage');
  }

  /**
   * Returns token.
   *
   * @return mixed|null
   *   Representation of a token or NULL if it was not set.
   */
  public function getToken() {
    return static::storage()->get($this->entity->id());
  }

  /**
   * Set token.
   *
   * @param object|string $value
   *   Representation of a token.
   * @param int $expire
   *   Expiration timestamp.
   */
  public function setToken($value, $expire) {
    if (!empty($value)) {
      static::storage()->setWithExpire($this->entity->id(), $value, $expire - REQUEST_TIME);
    }
  }

  /**
   * Returns a date when token will no longer be valid.
   *
   * @return \DateTime|null
   *   DateTime object or NULL if token isn't used or have no expiration.
   */
  public function getTokenExpiration() {
    return NULL;
  }

  /**
   * Trigger "sdk.callback" which must implement token requesting/receiving.
   *
   * @param string|Url|null $destination
   *   Destination path where user should be after processing.
   *
   * @return TrustedRedirectResponse
   *   An instance of response.
   */
  public function requestToken($destination = NULL) {
    if (empty($destination)) {
      $destination = \Drupal::request()->getUri();
    }
    elseif ($destination instanceof Url) {
      $destination = $destination->toString();
    }

    $_SESSION['destination'] = $destination;

    return new TrustedRedirectResponse($this->loginUrl());
  }

}
