<?php

namespace Drupal\simplenews_customizations\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\simplenews_customizations\Routing
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('simplenews.node_tab');

    if (!empty($route)) {
      $route->setDefault('_form', '\Drupal\simplenews_customizations\Form\SimplenewsNodeTabForm');
    }

  }

}
