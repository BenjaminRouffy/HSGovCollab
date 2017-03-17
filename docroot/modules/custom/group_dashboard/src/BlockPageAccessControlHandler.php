<?php

namespace Drupal\group_dashboard;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the block permissions.
 */
class BlockPageAccessControlHandler implements ContainerInjectionInterface {
  /**
   * The current user service.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Returns the service container.
   *
   * @return ContainerInterface
   *   The service container.
   */
  private function container() {
    return \Drupal::getContainer();
  }

  /**
   * Returns the current user.
   *
   * @return AccountInterface
   *   The current user.
   */
  protected function currentUser() {
    if (!$this->currentUser) {
      $this->currentUser = $this->container()->get('current_user');
    }

    return $this->currentUser;
  }

  /**
   * Access check for the block list.
   *
   * @return AccessResultInterface
   *    An access result
   */
  public function blockContentAccess() {
    $account = $this->currentUser();

    // Check if the user has the proper permissions.
    $access = AccessResult::allowedIfHasPermission($account, 'access block overview');

    return $access;
  }

  /**
   * Access check for the block list.
   *
   * @return AccessResultInterface
   *    An access result
   */
  public function blockContentTypesAccess() {
    $account = $this->currentUser();

    // Check if the user has the proper permissions.
    $access = AccessResult::allowedIfHasPermission($account, 'administer custom block content types');

    return $access;
  }

}
