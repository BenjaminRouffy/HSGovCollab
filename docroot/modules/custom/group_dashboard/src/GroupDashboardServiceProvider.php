<?php

namespace Drupal\group_dashboard;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service Provider for Group dashboard.
 */
class GroupDashboardServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('group.admin_path.route_subscriber');

    $definition->setClass('\Drupal\group_dashboard\Routing\GroupDashboardAdminRouteSubscriber');
  }

}
