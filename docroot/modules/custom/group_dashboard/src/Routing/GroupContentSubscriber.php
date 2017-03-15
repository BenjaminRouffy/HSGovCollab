<?php

namespace Drupal\group_dashboard\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Reacts to group content loader events.
 */
class GroupContentSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = [
      'entity.group_content.create_page',
      'entity.group_content.add_page',
      'entity.group_content.subgroup_relate_page',
    ];

    foreach ($routes as $item) {
      if ($route = $collection->get($item)) {
        /** @var \Symfony\Component\Routing\Route */
        $defaults = $route->getDefaults();
        if ('entity.group_content.subgroup_relate_page' == $item) {
          $defaults['_controller'] = '\Drupal\group_dashboard\Controller\GroupAdminSubgroupController::addPage';
        }
        else {
          $defaults['_controller'] = '\Drupal\group_dashboard\Controller\GroupAdminContentController::addPage';
        }
        $route->setDefaults($defaults);
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run after EntityRouteAlterSubscriber.
    // Weight should be higher than page_manager rout alter.
    $events[RoutingEvents::ALTER][] = ['onAlterRoutes', -880];
    return $events;
  }
}
