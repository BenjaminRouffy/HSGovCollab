<?php

namespace Drupal\p4h_views_plugins\Plugin\views\pager;

use Drupal\views\Plugin\views\pager\Mini;

/**
 * The plugin to handle mini pager.
 *
 * @ingroup views_pager_plugins
 *
 * @todo there should be used p4h_views_plugins own theme (views_mini_pager).
 *
 * @ViewsPager(
 *   id = "mini_with_items",
 *   title = @Translation("Mini pager with items"),
 *   short_title = @Translation("Mini Items"),
 *   help = @Translation("A simple pager containing previous and next links."),
 *   theme = "p4h_views_plugins"
 * )
 */
class MiniWithItems extends Mini {


  /**
   * {@inheritdoc}
   */
  public function render($input) {
    // The 1, 3 indexes are correct, see template_preprocess_pager().
    $tags = array(
      1 => $this->options['tags']['previous'],
      3 => $this->options['tags']['next'],
    );
    return array(
      '#theme' => $this->themeFunctions(),
      '#tags' => $tags,
      '#options' => [
        'per_page' => $this->options['items_per_page'],
        'total_rows' => $this->view->total_rows,
      ],
      '#element' => $this->options['id'],
      '#parameters' => $input,
      '#route_name' => !empty($this->view->live_preview) ? '<current>' : '<none>',
    );
  }

}
