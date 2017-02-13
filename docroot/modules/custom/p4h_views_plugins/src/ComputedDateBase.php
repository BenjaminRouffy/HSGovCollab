<?php

namespace Drupal\p4h_views_plugins;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ComputedDateBase.
 */
abstract class ComputedDateBase extends PluginBase implements ComputedDateInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\p4h_views_plugins\ComputedDateManagerInterface
   */
  public $manager;

  /**
   * ComputedDateBase constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\p4h_views_plugins\ComputedDateManagerInterface $manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ComputedDateManagerInterface $manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->manager = $manager;
  }

  /**
   * ContainerFactoryPluginInterface implementation.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.computed_date')
    );
  }

  /**
   * Get entity helper function.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   It is an entity.
   */
  protected function getEntity() {
    return $this->configuration['entity'];
  }

  /**
   * Add a specific value to a computed_date custom filed.
   */
  final public function updateValue() {
    $datetime = $this->getValue();
    if ($datetime instanceof DrupalDateTime) {
      /* @var $entity EntityInterface */
      $entity = $this->getEntity();
      $new_date = $this->manager->getTimestamp($datetime);
      $entity->set('computed_date', $new_date);
    }
  }

}
