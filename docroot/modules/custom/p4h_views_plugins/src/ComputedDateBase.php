<?php
/**
 * @file
 */

namespace Drupal\p4h_views_plugins;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ComputedDateBase extends PluginBase implements ComputedDateInterface, ContainerFactoryPluginInterface {

  public $manager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ComputedDateManagerInterface $manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->manager = $manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.computed_date')
    );
  }

  protected function getEntity() {
    return $this->configuration['entity'];
  }

  final public function updateValue() {
    $datetime = $this->getValue();
    if($datetime instanceof DrupalDateTime) {
      /* @var $entity EntityInterface */
      $entity = $this->getEntity();
      $new_date = $this->manager->getTimestamp($datetime);
      $entity->set('computed_date', $new_date);
    }
  }
}
