<?php

namespace Drupal\group_dashboard\Routing;

use Drupal\group\Routing\GroupAdminRouteSubscriber;
use Symfony\Component\Routing\RouteCollection;

/**
 * Sets the _admin_route for specific group-related routes.
 */
class GroupDashboardAdminRouteSubscriber extends GroupAdminRouteSubscriber {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    parent::alterRoutes($collection);

    $route = $collection->get('entity.group.collection');

    if (!empty($route)) {
      $route->setRequirements([
        '_permission' => 'access group overview',
      ]);
    }

    $route = $collection->get('block.admin_display');

    if (!empty($route)) {
      $route->addRequirements([
        '_custom_access' => '\\Drupal\\group_dashboard\\BlockPageAccessControlHandler::blockContentAccess',
      ]);
    }

    $route = $collection->get('entity.block_content_type.collection');

    if (!empty($route)) {
      $route->addRequirements([
        '_custom_access' => '\\Drupal\\group_dashboard\\BlockPageAccessControlHandler::blockContentTypesAccess',
      ]);
    }

    $route = $collection->get('system.admin_config');

    if (!empty($route)) {
      $route->setRequirements([
        '_permission' => 'access configuration page',
      ]);
    }
  }

}
