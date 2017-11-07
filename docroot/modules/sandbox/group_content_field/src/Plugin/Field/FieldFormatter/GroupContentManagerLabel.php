<?php

namespace Drupal\group_content_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\group\Entity\Group;

/**
 * Plugin implementation of the 'plugin_reference_id' formatter.
 *
 * @FieldFormatter(
 *   id = "group_content_manager_list",
 *   label = @Translation("Manager label"),
 *   field_types = {
 *     "group_content_item"
 *   }
 * )
 */
class GroupContentManagerLabel extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode):array {
    $elements = [];

    // Initialize field.
    $items->setValue(1);

    foreach ($items as $delta => $item) {
      $value = $item->getValue();

      /* @var Group $group */
      foreach (Group::loadMultiple($value['entity_gids']) as $group) {
        $group_bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($group->getEntityTypeId());
        $group_type = str_replace('_', ' ', $group->bundle());
        switch ($group_type) {
          // @todo add relation with real roles.
          case 'country':
            $elements[] = [
              '#type' => 'markup',
              '#markup' => $this->t('@label P4H @manager', [
                '@label' => $group->label(),
                '@manager' => $group->field_geographical_object->value ? 'Country Focal Person' : 'Area manager',
              ]),
            ];
            break;

          default:
            $elements[] = [
              '#type' => 'markup',
              '#markup' => $this->t('@label @group_type manager', [
                '@label' => $group->label(),
                '@group_type' => $group_bundles[$group->bundle()]['label'],
              ]),
            ];
            break;
        }
      }
    }

    return $elements;
  }

}
