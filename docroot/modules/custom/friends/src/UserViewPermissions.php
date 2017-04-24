<?php

namespace Drupal\friends;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides dynamic permissions of user view.
 */
class UserViewPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity view display storage.
   *
   * @var EntityStorageInterface
   */
  protected $entity_view_display;


  /**
   * {@inheritdoc}
   */
  public function __construct(EntityStorageInterface $entity_view_display) {
    $this->entity_view_display = $entity_view_display;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('entity_view_display')
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
    foreach ($this->entity_view_display->loadMultiple() as $id => $view_display) {
      if ('user' == $view_display->getTargetEntityTypeId()) {
        $permissions["view {$view_display->getMode()} view display"] = [
          'title' => $this->t('User: View @view_display view display', ['@view_display' => $view_display->getMode()]),
        ];
      }
    }

    return $permissions;
  }

}
