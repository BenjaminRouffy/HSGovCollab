<?php

namespace Drupal\form_alter_service;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Class FormAlterServiceProvider.
 */
class FormAlterServiceServiceProvider implements ServiceProviderInterface  {

  /**
   * Registers services to the container.
   *
   * @param ContainerBuilder $container
   *   The ContainerBuilder to register services to.
   */
  public function register(ContainerBuilder $container) {
    $container->addCompilerPass(new FormAlterHandlersPass());
  }

}
