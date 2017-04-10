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
   * @param \Drupal\group\Entity\GroupInterface $group
   */
  public function follow(GroupInterface $group) {
    /** @var \Drupal\group_following\GroupFollowingManagerInterface $manager */
    $manager = \Drupal::getContainer()->get('group_following.manager');

    $group_following = $manager->getFollowingByGroup($group);

    $group_following->follow($this->currentUser);
    return $this->redirect('entity.group.canonical', ['group' => $group->id()]);
  }

  /**
   * @param \Drupal\group\Entity\GroupInterface $group
   */
  public function unfollow(GroupInterface $group) {
    /** @var \Drupal\group_following\GroupFollowingManagerInterface $manager */
    $manager = \Drupal::getContainer()->get('group_following.manager');

    $group_following = $manager->getFollowingByGroup($group);

    $group_following->unfollow($this->currentUser);
    return $this->redirect('entity.group.canonical', ['group' => $group->id()]);
  }

}
