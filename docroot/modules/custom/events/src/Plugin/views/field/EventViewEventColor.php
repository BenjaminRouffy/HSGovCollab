<?php

namespace Drupal\events\Plugin\views\field;

use Drupal\node\Entity\Node;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * A handler to provide proper displays for event color.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("events_view_event_color")
 */
class EventViewEventColor extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $color = '';

    $relationship_entities = $values->_relationship_entities;
    // First check the referenced entity.
    if (isset($relationship_entities['node'])) {
      $node = $relationship_entities['node'];
    }
    else {
      $node = $values->_entity;
    }

    if ($node instanceof Node && $node->bundle() == 'event') {
      $color = $node->get('event_color')->getValue();
    }

    return $color;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function exists to override parent query function.
    // Do nothing.
  }

}
