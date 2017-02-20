<?php
/**
 * @file
 */

namespace Drupal\views_default_arguments\Plugin\views\argument_default;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;
use Drupal\node\Plugin\views\argument_default\Node;

/**
 * Default argument plugin to extract a node.
 *
 * @ViewsArgumentDefault(
 *   id = "group_by_node",
 *   title = @Translation("Group ID By Content")
 * )
 */
class GroupIDByContentNode extends Node implements CacheableDependencyInterface {

  /**
   * @var array
   */
  public $items = [];

  /**
   * @var NodeInterface
   */
  public $node = FALSE;

  protected function getGroups() {
    if (($this->node = $this->routeMatch->getParameter('node')) && $this->node instanceof NodeInterface) {
      $content_group = GroupContent::loadByEntity($this->node);
      $count_group = (bool) count($content_group);

      if ($count_group) {
        foreach ($content_group as $group) {
          $this->items[] = $group->gid->target_id;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    $this->getGroups();
    return $this->items ? implode('+', $this->items) : 'all';
  }
}
