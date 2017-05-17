<?php

namespace Drupal\events\Plugin\views\field;

use Colors\RandomColor;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * A handler to provide proper displays for profile current company.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("events_random_color")
 */
class ColorProcessed extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (!isset($this->cache[$values->nid])) {
      $this->cache[$values->nid] = RandomColor::one([
        'luminosity' => 'dark',
      ]);
    }
    return $this->cache[$values->nid];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function exists to override parent query function.
    // Do nothing.
  }

}
