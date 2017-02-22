<?php

namespace Drupal\group_dashboard\Controller;

use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\group\Entity\Controller\GroupListBuilder;

/**
 * Class RelatedEntitties.
 *
 * @package Drupal\group_dashboard\Controller
 */
class GroupAdminListBuilder extends GroupListBuilder implements EntityListBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return views_embed_view('group_admin', 'embed_1');
  }

}
