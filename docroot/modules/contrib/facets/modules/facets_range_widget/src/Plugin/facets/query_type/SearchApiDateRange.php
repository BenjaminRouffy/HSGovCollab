<?php

namespace Drupal\facets_range_widget\Plugin\facets\query_type;

use Drupal\facets\Plugin\facets\query_type\SearchApiDate;

/**
 * Support for date range facets within the Search API scope.
 *
 * @FacetsQueryType(
 *   id = "search_api_date_range",
 *   label = @Translation("Date Range"),
 * )
 */
class SearchApiDateRange extends SearchApiDate {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $query = $this->query;

    // Only alter the query when there's an actual query object to alter.
    if (!empty($query)) {
      $operator = $this->facet->getQueryOperator();
      $field_identifier = $this->facet->getFieldIdentifier();
      $exclude = $this->facet->getExclude();

      // Set the options for the actual query.
      $options = &$query->getOptions();
      $options['search_api_facets'][$field_identifier] = [
        'field' => $field_identifier,
        'limit' => $this->facet->getHardLimit(),
        'operator' => $this->facet->getQueryOperator(),
        'min_count' => $this->facet->getMinCount(),
        'missing' => FALSE,
      ];

      // Add the filter to the query if there are active values.
      $active_items = $this->facet->getActiveItems();

      if (count($active_items)) {
        $filter = $query->createConditionGroup($operator, ['facet:' . $field_identifier]);
        foreach ($active_items as $value) {
          $value0 = parent::calculateResultFilter($value[0]);
          $value1 = parent::calculateResultFilter($value[1]);
          $range0 = parent::calculateRange($value0['raw']);
          $range1 = parent::calculateRange($value1['raw']);
          $filter->addCondition($field_identifier, [$range0['start'], $range1['stop']], $exclude ? 'NOT BETWEEN' : 'BETWEEN');
        }
        $query->addConditionGroup($filter);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateResultFilter($value) {
    $result_filter = parent::calculateResultFilter($value);
    $range = parent::calculateRange($result_filter['raw']);
    $result_filter['raw'] = $range['start'] + floor(($range['stop'] - $range['start']) / 2);
    return $result_filter;
  }

  /**
   * Retrieve configuration: If the date should be displayed relatively.
   */
  protected function getDisplayRelative() {
    return FALSE;
  }

}
