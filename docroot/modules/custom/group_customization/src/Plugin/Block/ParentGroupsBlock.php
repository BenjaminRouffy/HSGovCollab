<?php

namespace Drupal\group_customization  \Plugin\Block;

use Drupal;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Driver\mysql\Select;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContentType;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a special block.
 *
 * @Block(
 *   id = "parent_groups_block",
 *   admin_label = @Translation("Parent groups block"),
 *   category = @Translation("Blocks")
 * )
 */
class ParentGroupsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $class = 'custom-bc-wrapper parent-groups-list-';

    if ($entity = \Drupal::request()->attributes->get('node')) {
      $class .= $entity->getEntityType()->id(). '-' . $entity->getType();
    }
    elseif ($entity = \Drupal::request()->attributes->get('group')) {
      $class .= $entity->getEntityType()->id() . '-' . $entity->getGroupType()->id();
    }
    else {
      return [];
    }

    $plugin_ids = array_keys(GroupContentType::loadByEntityTypeId($entity->getEntityType()->id()));

    $query = \Drupal::database()->select('group_content_field_data', 'gc');
    $query->leftJoin('group_graph', 'gp', 'gc.gid = gp.end_vertex');

    $result = $query->fields('gc', ['id', 'gid'])
      ->fields('gp', ['start_vertex', 'hops'])
      ->condition('gc.type', $plugin_ids, 'IN')
      ->condition('gc.entity_id', $entity->id())
      ->execute()
      ->fetchAllAssoc('id');

    $parents = [];

    foreach ($result as $id => $row) {
      if (!isset($parents[0][$row->gid])) {
        $parents[0][$row->gid] = $row->gid;
      }
      if (!empty($row->start_vertex)) {
        $parents[1][$row->start_vertex] = $row->start_vertex;
      }
    }

    $elements = [];

    if (!empty($parents)) {
      $elements['title'] = [
        '#type' => 'markup',
        '#markup' => t('Part of: '),
      ];

      $elements['title']['#prefix'] = '<div class="custom-bc-title">';
      $elements['title']['#suffix'] = '</div>';

      foreach ($parents as $parent) {
        /* @var GroupInterface $group */
        foreach (Group::loadMultiple($parent) as $group) {
          $id = $group->id();
          if ($group->getGroupType()
              ->id() != 'country' || ($group->hasField('field_group_status') && 'published' === $group->get('field_group_status')->value)
          ) {
            $elements[$id] = [
              '#type' => 'link',
              '#title' => $group->label(),
              '#url' => Url::fromRoute('entity.group.canonical', ['group' => $group->id()]),
            ];
          }
          else {
            $elements[$id] = [
              '#type' => 'markup',
              '#markup' => $group->label(),
            ];
          }
          $elements[$id]['#prefix'] = '<div class="custom-bc-item">';
          $elements[$id]['#suffix'] = '</div>';
        }
      }
    }

    return $elements + [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            $class,
          ]
        ],
        '#cache' => [
          'contexts' => [
            'route',
          ],
        ],
      ];
  }

}
