<?php

namespace Drupal\group_following\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 */
class GroupFollowController extends ControllerBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new GroupMembershipController.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @internal param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder The entity form builder.*   The entity form builder.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   */
  public function follow(GroupInterface $group) {

//    $group = $this->getContextValue('group');
//    $user = $this->getContextValue('current_user');
//
//    /** @var \Drupal\group\GroupMembership $membership */
//    $membership = $group->getMember($user);
//    $membership->getRoles();

    /** @var \Drupal\group\Plugin\GroupContentEnablerInterface $plugin */
    $plugin = $group->getGroupType()->getContentPlugin('group_membership');

    // Pre-populate a group membership with the current user.
    $group_content = GroupContent::create([
      'type' => $plugin->getContentTypeConfigId(),
      'gid' => $group->id(),
      'entity_id' => $this->currentUser->id(),
    ]);

    return $this->entityFormBuilder->getForm($group_content, 'group-join');
  }

  /**
   */
  public function unfollow(GroupInterface $group) {

//  /** @var \Drupal\group_following\GroupFollowingManagerInterface $manager */
//  $manager = \Drupal::getContainer()->get('group_following.manager');
//
//  $group_following = $manager->getFollowingByGroup(new Group());
//
//  $result = $group_following->getResultByAccount($account);
//
//  if($result->isFollower() == TRUE) {
//      $group_following->follow();
//      $group_following->unfollow();
//    }
    $group_content = $group->getMember($this->currentUser)->getGroupContent();
    return $this->entityFormBuilder->getForm($group_content, 'group-leave');
  }

}
