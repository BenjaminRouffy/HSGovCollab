<?php

namespace Drupal\ajax_comments;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\comment\CommentAccessControlHandler;

/**
 * Defines the custom access control handler for the comment entity type.
 *
 * @see \Drupal\comment\Entity\Comment
 */
class AjaxCommentsAccessHandler extends CommentAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\comment\CommentInterface|\Drupal\user\EntityOwnerInterface $entity */

    $comment_admin = $account->hasPermission('administer comments');
    if ($operation == 'approve') {
      return AccessResult::allowedIf($comment_admin && !$entity->isPublished())
        ->cachePerPermissions()
        ->addCacheableDependency($entity);
    }

    if ($comment_admin) {
      $access = AccessResult::allowed()->cachePerPermissions();
      return ($operation != 'view') ? $access : $access->andIf($entity->getCommentedEntity()
        ->access($operation, $account, TRUE));
    }

    switch ($operation) {
      case 'view':
        $access_result = AccessResult::allowedIf($account->hasPermission('access comments') && $entity->isPublished())
          ->cachePerPermissions()
          ->addCacheableDependency($entity)
          ->andIf($entity->getCommentedEntity()
            ->access($operation, $account, TRUE));
        if (!$access_result->isAllowed()) {
          $access_result->setReason("The 'access comments' permission is required and the comment must be published.");
        }

        return $access_result;

      case 'update':
        return AccessResult::allowedIf($account->id() && $account->id() == $entity->getOwnerId() && $entity->isPublished() && $account->hasPermission('edit own comments'))
          ->cachePerPermissions()
          ->cachePerUser()
          ->addCacheableDependency($entity);

      case 'delete':
        return AccessResult::allowedIf($account->id() && $account->id() == $entity->getOwnerId() && $entity->isPublished() && $account->hasPermission('delete own comments'))
          ->cachePerPermissions()
          ->cachePerUser()
          ->addCacheableDependency($entity);

      default:
        // No opinion.
        return AccessResult::neutral()->cachePerPermissions();
    }
  }
}
