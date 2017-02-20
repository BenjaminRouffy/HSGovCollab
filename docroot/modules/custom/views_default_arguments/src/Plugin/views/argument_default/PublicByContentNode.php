<?php
/**
 * @file
 */

namespace Drupal\views_default_arguments\Plugin\views\argument_default;


use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;

/**
 * Default argument plugin to extract a node.
 *
 * @ViewsArgumentDefault(
 *   id = "public_by_node",
 *   title = @Translation("Public By Content")
 * )
 */
class PublicByContentNode extends GroupIDByContentNode implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    $this->getGroups();
    if ($this->node && $this->node instanceof NodeInterface) {
      return empty($this->node->public_content->getValue()) ? 'all' : implode('+', $this->node->public_content->getValue());
    }
  }
}
