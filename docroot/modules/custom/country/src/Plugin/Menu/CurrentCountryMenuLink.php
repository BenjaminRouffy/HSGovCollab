<?php

namespace Drupal\country\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\group\Entity\Group;

class CurrentCountryMenuLink extends MenuLinkDefault implements ContainerFactoryPluginInterface {

  public function getTitle() {
    /* @var Group $group */
    $group = \Drupal::routeMatch()->getParameter('group');

    return empty($group) ? parent::getTitle() : $group->label();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
