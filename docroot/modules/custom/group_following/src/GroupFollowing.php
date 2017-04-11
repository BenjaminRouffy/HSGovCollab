<?php

namespace Drupal\group_following;

use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\GroupInterface;

/**
 * Class GroupFollowing.
 */
class GroupFollowing implements GroupFollowingInterface {

  /**
   * @var GroupFollowingManagerInterface
   */
  protected $groupFollowingManager;

  /**
   * @var GroupInterface
   */
  protected $group;

  /**
   * @var GroupFollowingResult[]
   */
  protected $accountFollowing;

  /**
   * GroupFollowing constructor.
   */
  function __construct(GroupFollowingManagerInterface $group_following_manager, GroupInterface $group) {
    $this->groupFollowingManager = $group_following_manager;
    $this->group = $group;
  }

  /**
   * Gets a group following result by user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   *
   * @return GroupFollowingResult
   *   Group result object.
   */
  public function getResultByAccount(AccountInterface $account) {
    if (!isset($this->accountFollowing[$account->id()])) {
      $this->accountFollowing[$account->id()] = new GroupFollowingResult($this, $account);
    }
    return $this->accountFollowing[$account->id()];
  }

  /**
   * Create/update a membership with a field_follower field.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   */
  public function follow(AccountInterface $account) {
    /** @var \Drupal\group\GroupMembership $group_membership */
    $group_membership = $this->group->getMember($account);

    $field_value = [
      'field_follower' => [
        'value' => 1,
      ],
    ];

    if ($group_membership) {
      /** @var \Drupal\group\Entity\GroupContentInterface $group_content */
      $group_content = $group_membership->getGroupContent();
      if ($group_content->hasField('field_follower')) {
        $group_content->set('field_follower', $field_value['field_follower'])
          ->save();
      }
    }
    else {
      /** @var \Drupal\user\UserInterface $user */
      $user = entity_load('user', $account->id());
      $this->group->addMember($user, $field_value);
    }
  }

  /**
   * Change a field_follower value to FALSE.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account object.
   */
  public function unfollow(AccountInterface $account) {
    /** @var \Drupal\group\GroupMembership $group_membership */
    $group_membership = $this->group->getMember($account);

    $field_value = [
      'field_follower' => [
        'value' => 0,
      ],
    ];

    if ($group_membership) {
      /** @var \Drupal\group\Entity\GroupContentInterface $group_content */
      $group_content = $group_membership->getGroupContent();
      if ($group_content->hasField('field_follower')) {
        $group_content->set('field_follower', $field_value['field_follower'])
          ->save();
      }
    }
    else {
      /** @var \Drupal\user\UserInterface $user */
      $user = entity_load('user', $account->id());
      $this->group->addMember($user, $field_value);
    }
  }

  /**
   * Counts a user's following based on group.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User account.
   *
   * @return int
   *   Count following based on group.
   */
  public function getFollowerByGroupForUser(AccountInterface $account) {
    /** @var GroupFollowingStorageInterface $storage */
    $storage = $this->groupFollowingManager->getStorage();
    return $storage->getFollowerByGroupForUser($this->group, $account);
  }

}
