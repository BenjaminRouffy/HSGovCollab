<?php
/**
 * Created by PhpStorm.
 * User: valerij
 * Date: 07.04.17
 * Time: 13:44
 */

namespace Drupal\group_following\Plugin\GroupContentDecorator;


use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\group_content_field\Plugin\GroupContentDecoratorBase;

/**
 * Class GroupContentDecoratorFollowing
 *
 * @GroupContentDecorator(
 *   id = "group_content_following",
 *   label = @Translation("Group following")
 * )
 */
class GroupContentDecoratorFollowing extends GroupContentDecoratorBase {

  /**
   * @var \Drupal\group\Plugin\GroupContentEnablerManager
   */
  protected $pluginManager;
  protected $groupType;

  /** @var \Drupal\group_following\GroupFollowingManagerInterface $followingManager */
  protected $followingManager;

  public function __construct($configuration) {
    parent::__construct($configuration);
    $this->pluginManager= \Drupal::service('plugin.manager.group_content_enabler');
    $this->groupType = \Drupal::entityTypeManager()->getStorage('group_type');
    $this->followingManager =  \Drupal::getContainer()->get('group_following.manager');
  }


  /**
   * Additional plugin spec field settings.
   */
  function fieldStorageSettings() {
    return [];
  }

  /**
   * Get default values.
   *
   * @return array
   *   Array with gids.
   */
  function getDefaultValues($parent_entity) {
    return $this->followingManager->getFollowedForUser($parent_entity, $this->groupContentItem->getSetting('group_type'));
  }

  function getBuildProperties($parent_entity) {
    return [];
  }

  /**
   * Method which assign selected content to group.
   */
  function createMemberContent($parent_entity, $add_gid) {
    $group_following = $this->followingManager->getFollowingByGroup(Group::load($add_gid));

    $group_following->follow($parent_entity);
  }

  /**
   * @inheritdoc
   */
  function removeMemberContent($parent_entity, $delete_gid) {
    $group_following = $this->followingManager->getFollowingByGroup(Group::load($delete_gid));

    $group_following->unfollow($parent_entity);
  }
}
