<?php

namespace Drupal\events\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\events\Routing
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach (['group.calendar', 'view.my_calendar.data_export_1'] as $route_name) {
      if ($route = $collection->get($route_name)) {
        $route->addRequirements([
          // @TODO Create custom access service (https://www.drupal.org/docs/8/api/routing-system/access-checking-on-routes)
          '_custom_access' => "\\Drupal\\events\\CalendarAccessControlHandler::checkMembershipAccess",
        ]);
      }
    }
  }

}
