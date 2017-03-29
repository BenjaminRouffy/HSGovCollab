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
 *   category = @Translation("Blocks"),
 *   context = {
 *     "parent_groups" = @ContextDefinition("entity:group", required = FALSE)
 *   }
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

    return $links + [
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
  public function generateNextPrevious(Node $node, $direction = 'next') {
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
    $query = \Drupal::database()->select('node_field_data', 'n')
      ->fields('n', ['nid'])
      ->condition('n.nid', $node->id(), $comparison_operator)
      ->condition('n.type', $node->getType())
      ->orderBy('n.created', $sort)
      ->range(0, 1)
      ->condition('n.langcode', $lang_code)
      ->addTag('node_access');

    $groups = $this->getContextValue('parent_groups');

    if (!empty($groups)) {
      $gids = [];

      foreach ($groups as $group) {
        $gids[] = $group->id();
      }

      $query->leftJoin('group_content_field_data', 'group_content', 'group_content.entity_id=n.nid');
      $query = $query->condition('group_content.type', '%-group_node-%', 'LIKE')
        ->condition('group_content.gid', $gids, 'IN');
    }

    $result = $query->execute()
    ->fetchCol();

    // If this is not the youngest (or oldest) node.
    if (!empty($result) && is_array($result)) {
      $result = array_values($result);
      $result = array_shift($result);
      $result_node = Node::load($result);
      $url = Url::fromRoute('entity.node.canonical', ['node' => $result], ['absolute' => TRUE]);

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
