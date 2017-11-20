<?php

namespace Drupal\p4h_views_plugins\Sort;

class SortMachine {
  protected $weight_table = [
    'geographical' => [
      // Start with non-geographical objects.
      '0' => 1,
      '1' => 2,
    ],
    'group_type' => [
      'region' => 1,
      'region_protected' => 1,
      'country' => 2,
      'country_protected' => 2,
      'project' => 3,
      'project_protected' => 3,
    ],
  ];

  /**
   * Sort items by $weight_table.
   *
   * @param array $filter_groups
   * @return array
   */
  public function sort($filter_groups) {

    usort($filter_groups, function($a, $b) {
      /**
       * @var SortItem $a
       * @var SortItem $b
       */
      $cmp = strnatcmp($a->getLabel(), $b->getLabel());

      $workspace = [
        0 =>[
          'group' => $a->getGroup(),
          'cmp' => $cmp,
        ],
        1 => [
          'group' => $b->getGroup(),
          'cmp' => -$cmp,
        ],
      ];

      foreach ($workspace as $key => $target) {
        $sum = '';
        $group_type = $target['group']->getGroupType()->id();

        if (!empty($target['group']->field_geographical_object) && $group_type !== 'project') {
          $sum .= $this->weight_table['geographical'][$target['group']->field_geographical_object->getValue()[0]['value']];
        }
        else {
          // Projects not depend on geographical goes to the end.
          $sum .= 3;
        }

        $sum .= $this->weight_table['group_type'][$group_type];

        $sum .= ($target['cmp'] + 1);

        $workspace[$key]['sum'] = $sum;
      }
      return $workspace[0]['sum'] - $workspace[1]['sum'];
    });

    $options = [];

    /**
     * @var SortItem $group_wrapper
     */
    foreach ($filter_groups as $group_wrapper) {
      $options[$group_wrapper->getGroup()->id()] = $group_wrapper->getLabel();
    }

    return $options;
  }
}
