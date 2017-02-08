<?php

/**
 * @file
 * Custom token for event slider first image.
 */

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function events_token_info() {
  $type['tokens']['node']['paragraphs_first_image'] = [
    'name' => new TranslatableMarkup('Paragraphs First Image URL'),
    'description' => new TranslatableMarkup('The url to the nodes paragraphs first image'),
    'needs-data' => 'node',
  ];

  return $type;
}

/**
 * Implements hook_tokens().
 */
function events_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'node' && !empty($data['node']) && array_key_exists('paragraphs_first_image', $tokens)) {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'paragraphs_first_image':
          // Get slider entity.
          $event_slider = $data['node']->get('field_event_slider')->first();
          $event_slider_reference = $event_slider->get('entity');
          $entity_adapter = $event_slider_reference->getTarget();
          $slider = $entity_adapter->getValue();
          // Get slider item entity.
          $slider_item = $slider->get('field_slider')->first();
          $slider_reference = $slider_item->get('entity');
          $slider_entity_adapter = $slider_reference->getTarget();
          $slider_item_entity = $slider_entity_adapter->getValue();
          // Get image entity.
          $slider_item_image = $slider_item_entity->get('field_content_image')->first();
          $slider_item_image_reference = $slider_item_image->get('entity');
          $slider_item_image_entity_adapter = $slider_item_image_reference->getTarget();
          $slider_item_image_entity = $slider_item_image_entity_adapter->getValue();
          $image = $slider_item_image_entity->getFileUri();

          $replacements[$original] = file_create_url($image);
          break;
      }
    }
  }

  return $replacements;
}
