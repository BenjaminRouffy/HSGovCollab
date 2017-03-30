<?php

namespace Drupal\group_customization\Access;

use Drupal\content_translation\Access\ContentTranslationOverviewAccess as BaseContentTranslationOverviewAccess;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Access\GroupAccessResult;

/**
 * Access check for entity translation overview.
 */
class ContentTranslationOverviewAccess extends BaseContentTranslationOverviewAccess {
  /**
   * {@inheritdoc}
   */
  public function access(RouteMatchInterface $route_match, AccountInterface $account, $entity_type_id) {
    $result = parent::access($route_match, $account, $entity_type_id);

    if ($entity_type_id === 'group') {
      $entity = $route_match->getParameter($entity_type_id);

      if ($entity && $entity->isTranslatable()) {
        $result = GroupAccessResult::allowedIfHasGroupPermission($entity, $account, 'translate group');
      }
    }

    return $result;
  }

}
