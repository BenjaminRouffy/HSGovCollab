<?php

namespace Drupal\user_registration\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class UserRegistrationResetLogin.
 *
 * @package Drupal\user_registration\Routing
 * Listens to the dynamic route events.
 */
class UserRegistrationResetLogin extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    if ($route = $collection->get('user.reset.login')) {
      /** @var \Symfony\Component\Routing\Route */
      $defaults = $route->getDefaults();
      $defaults['_controller'] = '\Drupal\user_registration\Controller\UserController::resetPassLogin';
      $route->setDefaults($defaults);
    }
    if ($route = $collection->get('user.reset')) {
      $route->setDefaults([
          '_controller' => '\Drupal\user\Controller\UserController::resetPassLogin'
      ]);
    }
  }
}
