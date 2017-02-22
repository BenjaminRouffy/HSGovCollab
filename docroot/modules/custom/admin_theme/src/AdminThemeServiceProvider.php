<?php

namespace Drupal\admin_theme;

use Drupal\admin_theme\Routing\AdminThemeAdminContext;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Decorates the admin context service.
 */
class AdminThemeServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->register('admin_theme.admin_context', AdminThemeAdminContext::class)
      ->addArgument(new Reference('admin_theme.admin_context.inner'))
      ->addArgument(new Reference('plugin.manager.condition'))
      ->addArgument(new Reference('config.factory'))
      ->setPublic(FALSE)
      ->setDecoratedService('router.admin_context');
  }

}
