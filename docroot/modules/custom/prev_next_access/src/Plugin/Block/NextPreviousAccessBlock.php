<?php
/**
 * @file
 * Contains \Drupal\prev_next_access\Plugin\Block\NextPreviousAccessBlock.
 */

namespace Drupal\prev_next_access\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Next Previous Access' block.
 *
 * @Block(
 *   id = "next_previous_access_block",
 *   admin_label = @Translation("Next Previous Access Block"),
 *   category = @Translation("Blocks")
 * )
 */
class NextPreviousAccessBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::request()->attributes->get('node');
    $link = "";
    $link .= $this->generateNextPrevious('prev', $node);
    $link .= $this->generateNextPrevious('next', $node);
    
    return [
      '#markup' => $link,
      '#cache' => [
        // For different users we will have different access rules.
        'contexts' => [
          'user',
          'route',
        ],
      ],
    ];
  }

  /**
   * Lookup the next or previous node.
   *
   * @param  string $direction either 'next' or 'previous'
   *   Direction for search.
   * @param \Drupal\node\Entity\Node $node
   *   Target node.
   *
   * @return string
   *   Rendered result.
   */
  private function generateNextPrevious($direction = 'next', Node $node) {
    $comparison_operator = $sort = $display_text = '';

    if ($direction === 'next') {
      $comparison_operator = '>';
      $sort = 'ASC';
      $display_text = t('Next story');
    }
    elseif ($direction === 'prev') {
      $comparison_operator = '<';
      $sort = 'DESC';
      $display_text = t('Previous story');
    }

    // Lookup 1 node younger (or older) than the current node.
    $query = \Drupal::entityQuery('node');

    $next = $query->condition('created', $node->getCreatedTime(), $comparison_operator)
      ->condition('type', $node->getType())
      ->sort('created', $sort)
      ->range(0, 1)
      ->accessCheck()
      ->execute();

    // If this is not the youngest (or oldest) node.
    if (!empty($next) && is_array($next)) {
      $next = array_values($next);
      $next = array_shift($next);
      $url = Url::fromRoute('entity.node.canonical', ['node' => $next], ['absolute' => TRUE]);

      return Link::fromTextAndUrl($display_text, $url)->toString();
    }

    return '';
  }
}
