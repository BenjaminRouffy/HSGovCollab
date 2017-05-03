<?php

namespace Drupal\group_dashboard;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\group\Entity\GroupTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides dynamic permissions of user view.
 */
class RelatedEntitiesPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * @var GroupTypeInterface
   */
  protected $groupType;


  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigEntityStorageInterface $group_type) {
    $this->groupType = $group_type;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('group_type')
    );
  }

  /**
   * Return the permissions for the user view.
   *
   * @return array
   *   Array of permissions.
   */
  public function permissions() {
    $permissions = [];

    /* @var EntityViewDisplay $view_display */
    foreach ($this->groupType->loadMultiple() as $group_type) {
      foreach ($group_type->getInstalledContentPlugins() as $plugin) {
        $permissions["access to relate {$plugin->getContentTypeConfigId()}"] = [
          'title' => $this->t('Group type: Access to relate @plugin', ['@plugin' => $plugin->getContentTypeLabel()]),
        ];
      }
    }

    return $permissions;
  }

}
