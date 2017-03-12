<?php

namespace Drupal\user_registration\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
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

    // The 'user.reset.login' route can be also added for testing.
    foreach (['user.reset'] as $item) {
      if ($route = $collection->get($item)) {
        /** @var \Symfony\Component\Routing\Route */
        $defaults = $route->getDefaults();
        $defaults['_controller'] = '\Drupal\user_registration\Controller\UserController::resetPassLogin';
        $route->setDefaults($defaults);
      }
    }

    if ($route = $collection->get('page_manager.page_view_sign_up_confirmation')) {
      $route->setRequirement('_user_is_onetime_access', 'user_registration.login_onetime');
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run after EntityRouteAlterSubscriber.
    // Wegth should be higher than page_manager rout alter.
    $events[RoutingEvents::ALTER][] = ['onAlterRoutes', -180];
    return $events;
  }
}
