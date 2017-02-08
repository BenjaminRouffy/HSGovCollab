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
          if (!empty($event_slider)) {
            $event_slider_reference = $event_slider->get('entity');
            $entity_adapter = $event_slider_reference->getTarget();
            $slider = $entity_adapter->getValue();

            if ($slider->hasField('field_slider')) {
              // Get image from nested paragraph "Slider".
              $slider_entity = get_nested_paragraph_entity($slider, 'field_slider');
              $image_entity = get_nested_paragraph_entity($slider_entity, 'field_content_image');
            }
            elseif ($slider->hasField('field_content_image')) {
              // Get image from paragraph "Image".
              $image_entity = get_nested_paragraph_entity($slider, 'field_content_image');
            }

            $image = $image_entity->getFileUri();
            $replacements[$original] = file_create_url($image);
          }

          break;
      }
    }
  }

  return $replacements;
}

/**
 * Get nested entity from paragraph's entity field.
 *
 * @param object $entity
 *   Paragraph's entity object.
 * @param string $field
 *   Entity Field name.
 *
 * return $nested_entity
 *   Return nested entity object.
 */
function get_nested_paragraph_entity($entity, string $field) {
  $slider_item_image = $entity->get($field)->first();
  $slider_item_image_reference = $slider_item_image->get('entity');
  $slider_item_image_entity_adapter = $slider_item_image_reference->getTarget();
  $nested_entity = $slider_item_image_entity_adapter->getValue();

  return $nested_entity;
}
