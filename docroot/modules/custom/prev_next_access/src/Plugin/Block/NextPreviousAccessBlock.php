<?php

namespace Drupal\prev_next_access\Plugin\Block;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\SafeMarkup;
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
    $links[] = $this->generateNextPrevious($node, 'prev');
    $links[] = $this->generateNextPrevious($node);

    if (!(empty($links[0]) && empty($links[1]))) {
      $links['#attributes'] = [
        'class' => [
          'node-pager',
        ],
      ];
      $links['#type'] = 'container';
    }

    return [
      $links,
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
   * @param \Drupal\node\Entity\Node $node
   *   Target node.
   * @param string $direction
   *   Direction for search('next' or 'prev').
   *
   * @return string
   *   Rendered result.
   */
  private function generateNextPrevious(Node $node, $direction = 'next') {
    $comparison_operator = $sort = $display_text = $direction_arrow = '';

    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();

    if ($direction === 'next') {
      $comparison_operator = '>';
      $sort = 'ASC';
      $display_text = t('Next story');
      $direction_arrow = 'right';
    }
    elseif ($direction === 'prev') {
      $comparison_operator = '<';
      $sort = 'DESC';
      $display_text = t('Previous story');
      $direction_arrow = 'left';
    }

    // Lookup 1 node younger (or older) than the current node.
    $query = \Drupal::entityQuery('node');
    $next = $query->condition('nid', $node->id(), $comparison_operator)
      ->condition('type', $node->getType())
      ->sort('created', $sort)
      ->range(0, 1)
      ->condition('langcode', $lang_code)
      ->accessCheck()
      ->execute();

    // If this is not the youngest (or oldest) node.
    if (!empty($next) && is_array($next)) {
      $next = array_values($next);
      $next = array_shift($next);
      $result_node = Node::load($next);
      $url = Url::fromRoute('entity.node.canonical', ['node' => $next], ['absolute' => TRUE]);

      $display_text = new FormattableMarkup('<span>@display_text</span><p>@text</p>', [
        '@display_text' => $display_text,
        '@text' => $result_node->getTranslation($lang_code)->getTitle(),
      ]);

      return Link::fromTextAndUrl($display_text, $url)->toRenderable() + [
        '#attributes' => [
          'class' => [
            "$direction-wrapper",
            'font-social-icon',
            "arrow-$direction_arrow-icon",
          ],
        ],
      ];
    }

    return [];
  }

}
