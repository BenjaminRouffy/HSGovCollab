<?php

namespace Drupal\search_customization\Plugin\search_api\display;
use Drupal\search_api\Plugin\search_api\display\ViewsBlock;

/**
 * Represents a Views block display.
 *
 * @SearchApiDisplay(
 *   id = "views_block_with_path",
 *   views_display_type = "block_with_path",
 *   deriver = "Drupal\search_api\Plugin\search_api\display\ViewsDisplayDeriver"
 * )
 */
class ViewsBlockWithPath extends ViewsBlock { }
