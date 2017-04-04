<?php

namespace Drupal\admin_customizations\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Reacts to group content loader events.
 */
class TaxonomyTermSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.taxonomy_term.canonical')) {
      $route->setRequirements([
        '_permission' => 'access taxonomy term page',
      ]);
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
