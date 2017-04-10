<?php
/**
 * Created by PhpStorm.
 * User: valerij
 * Date: 07.04.17
 * Time: 13:44
 */

namespace Drupal\group_following\Plugin\GroupContentDecorator;


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

  public function __construct($configuration) {
    parent::__construct($configuration);
    $this->pluginManager= \Drupal::service('plugin.manager.group_content_enabler');
    $this->groupType = \Drupal::entityTypeManager()->getStorage('group_type');
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
    // TODO: Implement getDefaultValues() method.
  }

  function getBuildProperties($parent_entity) {
    // TODO: Implement getBuildProperties() method.
  }
  /**
   * Method which assign selected content to group.
   */
  function createMemberContent($parent_entity, $add_gid) {
    $properties = $this->getBuildProperties($parent_entity);

    $properties['gid'] = $add_gid;

    $result = \Drupal::entityTypeManager()
      ->getStorage('group_content')
      ->loadByProperties($properties);

    if (empty($result)) {
      // TODO Add only role if membership exist.
      GroupContent::create($properties)->save();
    }
  }

  /**
   * @inheritdoc
   */
  function removeMemberContent($parent_entity, $delete_gid) {
    $properties = $this->getBuildProperties($parent_entity);

    $properties['gid'] = $delete_gid;
    $result = \Drupal::entityTypeManager()
      ->getStorage('group_content')
      ->loadByProperties($properties);

    foreach ($result as $group_content) {
      // TODO Remove only role.
      $group_content->delete();
    }
  }
}
