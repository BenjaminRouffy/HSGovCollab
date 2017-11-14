<?php

namespace Drupal\facets_range_widget\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Result\Result;

/**
 * Provides a processor that adds all range values between an min and max range.
 *
 * @FacetsProcessor(
 *   id = "date_range_slider",
 *   label = @Translation("Date range slider"),
 *   description = @Translation("Add date range results for all the steps between min and max range."),
 *   stages = {
 *     "pre_query" = 5,
 *     "post_query" = 5,
 *     "build" = 5
 *   }
 * )
 */
class DateRangeSliderProcessor extends RangeSliderProcessor {

  /**
   * {@inheritdoc}
   */
  public function postQuery(FacetInterface $facet) {
    $widget = $facet->getWidgetInstance();
    $config = $widget->getConfiguration();

    // Nothing to process if no results.
    if (!$results = $facet->getResults()) {
      return;
    }
    ksort($results);

    if ($config['min_type'] == 'fixed') {
      $min = $config['min_value'];
      $max = $config['max_value'];
    }
    else {
      $min = reset($results)->getRawValue();
      $max = end($results)->getRawValue();
    }

    $step = $config['step'];
    $max = $max + ($max % $step);

    /** @var \Drupal\facets\QueryType\QueryTypePluginManager $plugin_service */
    $query_type_plugin_manager = \Drupal::service('plugin.manager.facets.query_type');
    /** @var \Drupal\facets_range_widget\Plugin\facets\query_type\SearchApiDateRange $query_type_plugin */
    $query_type_plugin = $query_type_plugin_manager->createInstance('search_api_date_range', ['query' => null, 'facet' => $facet]);

    // Set active items again if there are active values.
    $active_items = $facet->getActiveItems() ?: [[]];

    // Creates an array of all results between min and max by the step from the
    // configuration.
    $new_results = [];
    for ($i = $min; $i <= $max; $i += $step) {
      $result_filter = $query_type_plugin->calculateResultFilter($i);
      foreach ([$i, $result_filter['raw']] as $key) {
        // Optimization.
        if (isset($results[$key]) && $results[$key]->getRawValue() === $i) {
          $result = $results[$key];
          break;
        }
        else {
          $count = isset($results[$key]) ? $results[$key]->getCount() : 0;
          $result = new Result($i, $result_filter['display'], $count);
        }
      }
      $result->setActiveState(in_array($i, $active_items[0]));
      $new_results[$i] = $result;
    }

    // Overwrite the current facet values with the generated results.
    $facet->setResults($new_results);
  }

}
