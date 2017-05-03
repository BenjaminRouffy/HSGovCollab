<?php

namespace Drupal\events\Plugin\views\style;

use Drupal\rest\Plugin\views\style\Serializer;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "event_serializer",
 *   title = @Translation("Event Serializer"),
 *   help = @Translation("Serializes views row data using the Event Serializer component."),
 *   display_types = {"data"}
 * )
 */
class EventSerializer extends Serializer {
  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    // If the Data Entity row plugin is used, this will be an array of entities
    // which will pass through Serializer to one of the registered Normalizers,
    // which will transform it to arrays/scalars. If the Data field row plugin
    // is used, $rows will not contain objects and will pass directly to the
    // Encoder.
    foreach ($this->view->result as $row_index => $row) {
      $this->view->row_index = $row_index;
      $rows[] = $this->view->rowPlugin->render($row);
    }
    unset($this->view->row_index);

    // Rewrite row's period values to correct JSON format.
    foreach ($rows as $key => $row) {
      if (!empty($row['period'])) {
        $row['start'] = $row['period']['start'];
        $row['end'] = $row['period']['end'];
        unset($row['period']);
        $rows[$key] = $row;
      }
    }

    // Get the content type configured in the display or fallback to the
    // default.
    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }
    return $this->serializer->serialize($rows, $content_type, ['views_style_plugin' => $this]);
  }
}
