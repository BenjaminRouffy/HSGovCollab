<?php

namespace Drupal\country\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\group\Entity\Group;

class CountryMenuLink extends MenuLinkDefault implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
