<?php


namespace Drupal\group_content_field\Plugin;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group\Entity\GroupContent;

/**
 * Created by PhpStorm.
 * User: valerij
 * Date: 07.04.17
 * Time: 14:22
 */
abstract class GroupContentDecoratorBase implements GroupContentDecoratorInterface {
  use StringTranslationTrait;
  protected $configuration;
  /**
   * @var \Drupal\group_content_field\Plugin\Field\FieldType\GroupContentItem
   */
  protected $groupContentItem;

  /**
   * GroupContentDecoratorBase constructor.
   * @param $configuration
   */
  public function __construct($configuration) {
    $this->configuration = $configuration;
    $this->groupContentItem = $configuration['group_content_item'];
  }

  /**
   * Plugin id.
   */
  public function getPluginId() {
    return $this->configuration['id'];
  }

  /**
   * Plugin label.
   */
  public function getLabel() {
    return $this->configuration['label'];
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
