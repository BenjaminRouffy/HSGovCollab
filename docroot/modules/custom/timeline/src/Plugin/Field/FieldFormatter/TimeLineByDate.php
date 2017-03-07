<?php

namespace Drupal\timeline\Plugin\Field\FieldFormatter;

use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Plugin implementation of the 'Timeline_by_date' formatter.
 *
 * @FieldFormatter(
 *   id = "Timeline_by_date",
 *   label = @Translation("Time line (Sort by date)"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class TimeLineByDate extends EntityReferenceRevisionsEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /* @var Paragraph $entity */
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => &$entity) {
      if ($entity->hasField('field_sort_by_date') && !empty($entity->get('field_sort_by_date')->getValue()[0]['value'])) {
        $contents = $entity->get('field_time_line_item')->getValue();
        $render_item = [];

        foreach ($contents as $index => $item) {
          /* @var Paragraph $time_line_item */
          $time_line_item = \Drupal::entityTypeManager()->getStorage('paragraph')->load($item['target_id']);
          $bundle = $time_line_item->bundle();

          switch ($bundle) {
            case 'custom_content':
              $render_item[$index] = strtotime($time_line_item->get('field_date')->getValue()[0]['value']);
              break;

            case 'exist_content':
            case 'timeline_social':
            case 'country_and_project':
              $entity_type = 'country_and_project' === $bundle ? 'group' : 'node';
              /* @var Paragraph $exist_content */
              $exist_content = \Drupal::entityTypeManager()->getStorage('paragraph')->load($item['target_id']);
              $exist_content = $exist_content->get("field_$entity_type")->getValue();

              if (!empty($exist_content)) {
                $exist_content = \Drupal::entityTypeManager()->getStorage($entity_type)->load(reset($exist_content)['target_id']);

                if (!empty($exist_content)) {
                  $date = $exist_content->get('computed_date')->getValue();

                  if (!empty($date)) {
                    $render_item[$index] = reset($date)['value'];
                  }
                }
              }
              break;
          }
        }

        arsort($render_item);
        $entity->get('field_time_line_item')->setValue($this->sortItem($contents, $render_item));
      }
    }

    return parent::viewElements($items, $langcode);
  }

  /**
   * Sort array by keys based on another array.
   *
   * @param array $items
   *   Default items.
   * @param array $order
   *   Order array.
   *
   * @return array
   *   The array after sorting.
   */
  private function sortItem(array $items, array $order) {
    $ordered = [];

    foreach ($order as $key => $value) {
      if (array_key_exists($key, $items)) {
        $ordered[$key] = $items[$key];
        unset($items[$key]);
      }
    }

    return $ordered + $items;
  }

}
