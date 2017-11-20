<?php

namespace Drupal\notifications\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Notification Plugin plugins.
 */
abstract class NotificationPluginBase extends PluginBase implements ContainerFactoryPluginInterface, NotificationPluginInterface {

  /**
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\user\UserDataInterface $user_data
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UserDataInterface $user_data, AccountProxyInterface $account_proxy) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userData = $user_data;
    $this->currentUser = $account_proxy->getAccount();
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      \Drupal::service('user.data'),
      \Drupal::currentUser()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function update() {
    $this->set(time());
  }

  /**
   * {@inheritdoc}
   */
  protected function get($default = 0) {
    return $this->userData->get('notifications', $this->currentUser->id(), $this->getPluginId()) ?: $default;
  }

  /**
   * {@inheritdoc}
   */
  protected function set($value) {
    $this->userData->set('notifications', $this->currentUser->id(), $this->getPluginId(), $value);
  }

  /**
   * {@inheritdoc}
   */
  protected function setSettings(array &$build, $array = []) {
    $build['#attached']['drupalSettings']['notifications'][$this->getPluginId()] = $array + $this->defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  private function defaultSettings() {
    return ['selector' => $this->getSelector()];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSelector() {
    return $this->pluginDefinition['selector'] ?: $this->getPluginId();
  }

}
