<?php

namespace Drupal\group_following\Plugin\views\filter;

use Drupal\Core\Database\Query\Condition;
use Drupal\views\Plugin\views\filter\BooleanOperator as DefaultBooleanOperator;

/**
 * Simple filter to handle matching of boolean values
 *
 * Definition items:
 * - label: (REQUIRED) The label for the checkbox.
 * - type: For basic 'true false' types, an item can specify the following:
 *    - true-false: True/false (this is the default)
 *    - yes-no: Yes/No
 *    - on-off: On/Off
 *    - enabled-disabled: Enabled/Disabled
 * - accept null: Treat a NULL value as false.
 * - use_equal: If you use this flag the query will use = 1 instead of <> 0.
 *   This might be helpful for performance reasons.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("boolean_string2")
 */
class BooleanOperator extends DefaultBooleanOperator   {

  protected function queryOpBoolean($field, $query_operator = self::EQUAL) {
    if ($this->accept_null) {
      if ($query_operator === self::EQUAL) {
        $condition = (new Condition('OR'))
          ->condition($field, 0, $query_operator)
          ->isNull($field);
      }
      else {
        $condition = (new Condition('AND'))
          ->condition($field, 0, $query_operator)
          ->isNotNull($field);
      }
      $this->query->addWhere($this->options['group'], $condition);
    }
    else {
      $this->query->addWhere($this->options['group'], $field, 0, $query_operator);
    }
  }

}
