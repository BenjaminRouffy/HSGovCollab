<?php

namespace Drupal\group_dashboard\Controller;

use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\group\Entity\Controller\GroupContentListBuilder;

/**
 * Class RelatedEntitties.
 *
 * @package Drupal\group_dashboard\Controller
 */
class GroupRelatedEntitiesListBuilder extends GroupContentListBuilder implements EntityListBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return views_embed_view('group_content', 'embed_1', $this->group->id());
  }

}
