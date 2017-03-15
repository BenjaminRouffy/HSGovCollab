<?php

namespace Drupal\form_alter_service;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FormAlterHandlersPass implements CompilerPassInterface {

  /**
   *
   * Example (YAML):
   * @code
   * tags:
   *   - { name: form_alter[, form_id: node_news_edit_form] }
   * @endcode
   *
   * @param ContainerBuilder $container
   */
  public function process(ContainerBuilder $container) {
    if (!$container->hasDefinition('form_alter_service.alter')) {
      return;
    }
    $definition = $container->getDefinition('form_alter_service.alter');
    $taggedServices = $container->findTaggedServiceIds('form_alter');

    foreach ($taggedServices as $id => $tags) {
      foreach ($tags as $tid => $attributes) {
        $definition->addMethodCall('addFormAlter', [
          new Reference($id),
          isset($attributes["form_id"]) ? $attributes["form_id"] : 'match'
        ]);
      }
    }
  }
}
