<?php

namespace Drupal\friends\Plugin\FieldPermissionType;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\field_permissions\Annotation\FieldPermissionType;
use Drupal\field_permissions\Plugin\FieldPermissionType\Base;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Defines custom access for fields.
 *
 * @FieldPermissionType(
 *   id = "friends",
 *   title = @Translation("Friends"),
 *   description = @Translation("Only author, administrators and friends can view."),
 *   weight = 50
 * )
 */
class FriendsAccess extends Base {

  /**
   * {@inheritdoc}
   */
  public function hasFieldAccess($operation, EntityInterface $entity, AccountInterface $account) {
    if ($entity instanceof EntityOwnerInterface) {
      $entity = $entity->getOwner();
    }

    return self::checkAccess($operation, $entity, $account);
  }

  /**
   * Determine if access to the field is granted for a given account.
   *
   * @param string $operation
   *   The operation to check. Either 'view' or 'edit'.
   * @param EntityInterface $entity
   *   The entity the field is attached to.
   * @param AccountInterface $account
   *   The user to check access for.
   *
   * @return bool
   *   The access result.
   */
  public static function checkAccess($operation, EntityInterface $entity, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if ($account->hasPermission('view private user profile')) {
          return TRUE;
        }

        if ($entity->hasField('field_disclose_your_personal_det')) {
          if (1 == $entity->get('field_disclose_your_personal_det')->value) {
            return TRUE;
          }
        }

        if ($entity instanceof UserInterface) {
          return $entity->id() == $account->id();
        }

        // @todo Add implementation by relation to user.
        return  FALSE;
        break;

      case 'edit':
        if ($account->hasPermission('edit private user profile')) {
          return TRUE;
        }

        // Users can access the field when creating new entities.
        if ($entity->isNew()) {
          return TRUE;
        }

        if ($entity instanceof UserInterface) {
          return $entity->id() == $account->id();
        }
        break;
    }
  }

}
