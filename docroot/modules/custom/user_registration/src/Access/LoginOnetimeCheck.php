<?php

namespace Drupal\user_registration\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;

/**
 * Determines access to routes based on login status of current user.
 */
class LoginOnetimeCheck implements AccessInterface {

  /**
   * Checks access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, Route $route) {
    // @TODO Maybe later we will add a custom verification of access to page_manager.page_view_sign_up_confirmation page.
    return AccessResult::allowedIf((bool) $token = TRUE)
      ->addCacheContexts(['user.roles:authenticated']);
  }

}
