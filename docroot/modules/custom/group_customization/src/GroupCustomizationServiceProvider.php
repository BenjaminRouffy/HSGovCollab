<?php

namespace Drupal\group_customization;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service Provider for Group dashboard.
 */
class GroupCustomizationServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('content_translation.overview_access');

    $definition->setClass('\Drupal\group_customization\Access\ContentTranslationOverviewAccess');
  }

}
