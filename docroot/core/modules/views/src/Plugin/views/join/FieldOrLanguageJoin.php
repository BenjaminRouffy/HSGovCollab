<?php

namespace Drupal\views\Plugin\views\join;

use Drupal\Core\Database\Query\SelectInterface;

/**
 * Implementation for the "field OR language" join.
 *
 * If the extra conditions contain either .langcode or .bundle,
 * they will be grouped and joined with OR. All bundles the field
 * appears on as untranslatable are included in $this->extra.
 *
 * @ingroup views_join_handlers
 *
 * @ViewsJoin("field_or_language_join")
 */
class FieldOrLanguageJoin extends JoinPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function joinAddExtra(&$arguments, &$condition, $table, SelectInterface $select_query, $left_table = NULL) {
    if (is_array($this->extra)) {
      $extras = [];
      foreach ($this->extra as $extra) {
        $extras[] = $this->buildExtra($extra, $arguments, $table, $select_query, $left_table);
      }

      if ($extras) {
        // Remove and store the langcode OR bundle join condition extra.
        $language_bundle_conditions = [];
        foreach ($extras as $key => &$extra) {
          if (strpos($extra, '.langcode') !== FALSE || strpos($extra, '.bundle') !== FALSE) {
            $language_bundle_conditions[] = $extra;
            unset($extras[$key]);
          }
        }

        if (count($extras) == 1) {
          $condition .= ' AND ' . array_shift($extras);
        }
        else {
          $condition .= ' AND (' . implode(' ' . $this->extraOperator . ' ', $extras) . ')';
        }

        // Tack on the langcode OR bundle join condition extra.
        if (!empty($language_bundle_conditions)) {
          $condition .= ' AND (' . implode(' OR ', $language_bundle_conditions) . ')';
        }
      }
    }
    elseif ($this->extra && is_string($this->extra)) {
      $condition .= " AND ($this->extra)";
    }
  }
}
