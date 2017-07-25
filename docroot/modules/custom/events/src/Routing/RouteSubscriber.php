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
  protected function alterRoutes( RouteCollection $collection ) {
    if ($route = $collection->get('group.calendar')) {
      $route->addRequirements(['_calendar_access_check' => 'TRUE']);
    }
    //if ($route = $collection->get('view.my_calendar.data_export_1')) {
    //  $route->addRequirements(['_calendar_export_access_check' => 'TRUE']);
    //}
  }

}
