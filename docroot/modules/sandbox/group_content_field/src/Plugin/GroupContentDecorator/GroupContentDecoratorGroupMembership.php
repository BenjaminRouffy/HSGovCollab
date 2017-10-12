<?php
/**
 * Created by PhpStorm.
 * User: valerij
 * Date: 07.04.17
 * Time: 13:44
 */

namespace Drupal\group_content_field\Plugin\GroupContentDecorator;


use Drupal\group\Entity\GroupContent;
use Drupal\group_content_field\Plugin\GroupContentDecoratorBase;

/**
 * Class GroupContentDecoratorNode
 *
 * @GroupContentDecorator(
 *   id = "group_content_group_membership",
 *   label = @Translation("Group membership")
 * )
 */
class GroupContentDecoratorGroupMembership extends GroupContentDecoratorBase {
  protected $groupRole;

  public function __construct($configuration) {
    parent::__construct($configuration);
    $this->groupRole = \Drupal::entityTypeManager()->getStorage('group_role');
  }

  public function getBuildProperties($parent_entity) {
    return [
      'type' => $this->groupContentItem->getSetting('group_type') . '-group_membership',
      'entity_id' => $parent_entity->id(),
      'group_roles' => $this->groupContentItem->getSetting('group_roles'),
    ];
  }

  /**
   * Additional plugin spec field settings.
   */
  function fieldStorageSettings() {
    $element = [];
    $group_roles = $this->groupRole->loadMultiple();
    $options = [];

    foreach ($group_roles as $group_role) {
      $options[$group_role->id()] = ucfirst($group_role->getGroupTypeId()) . ': ' . $group_role->label();
    }

    $element['group_roles'] = [
      '#type' => 'radios',
      '#title' => $this->t('Group type'),
      '#options' => $options,
      '#default_value' => $this->groupContentItem->getSetting('group_roles'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * @inheritdoc
   *
   * Special care: don't create group content if already exist.
   */
  function createMemberContent($parent_entity, $add_gid) {
    $properties = $this->getBuildProperties($parent_entity);

    // Try to find exist group content.
    unset($properties['group_roles']);
    $group_role = $this->groupContentItem->getSetting('group_roles');

    $properties['gid'] = $add_gid;

    $result = \Drupal::entityTypeManager()
      ->getStorage('group_content')
      ->loadByProperties($properties);

    if (empty($result)) {
      $properties['group_roles'] = $group_role;
      GroupContent::create($properties)->save();
    }
    else {
      /* @var GroupContent $group_content */
      foreach ($result as $group_content) {
        $group_content->group_roles->setValue($group_role);
        $group_content->save();
      }
    }
  }

  /**
   * @inheritdoc
   *
   * Special care: don't remove group content, just remove role.
   */
  function removeMemberContent($parent_entity, $delete_gid) {
    $properties = $this->getBuildProperties($parent_entity);
    $group_role = $this->groupContentItem->getSetting('group_roles');

    $properties['gid'] = $delete_gid;

    $result = \Drupal::entityTypeManager()
      ->getStorage('group_content')
      ->loadByProperties($properties);

    foreach ($result as $group_content) {
      foreach($group_content->group_roles as $delta => $item)  {
        if ($item->getValue()['target_id'] == $group_role) {
          unset($group_content->group_roles[$delta]);
          $group_content->save();
          break;
        }
      }
    }
  }

}
