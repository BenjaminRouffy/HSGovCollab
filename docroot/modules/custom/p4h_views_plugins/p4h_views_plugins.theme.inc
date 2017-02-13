<?php

/**
 * @file
 * Preprocessors and helper functions to make theming easier.
 */

/**
 * Prepares variables for views mini-pager templates.
 *
 * Default template: views-mini-pager.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - tags: Provides link text for the next/previous links.
 *   - element: The pager's id.
 *   - parameters: Any extra GET parameters that should be retained, such as
 *     exposed input.
 *
 * @see template_preprocess_views_mini_pager
 */
function template_preprocess_p4h_views_plugins(array &$variables) {
  global $pager_page_array, $pager_total;

  \Drupal::moduleHandler()->loadInclude('views', 'inc', 'views.theme');
  template_preprocess_views_mini_pager($variables);

  $element = $variables['element'];

  // Current is the page we are currently paged to.
  $curr_page = $pager_page_array[$element];
  $per_page = $variables['options']['per_page'];

  $variables['items']['min'] = ($curr_page * $per_page) + 1;
  $variables['items']['max'] = (($curr_page + 1) * $per_page);
  if ($variables['items']['max'] > $variables['options']['total_rows']) {
    $variables['items']['max'] = $variables['options']['total_rows'];
  }

}
