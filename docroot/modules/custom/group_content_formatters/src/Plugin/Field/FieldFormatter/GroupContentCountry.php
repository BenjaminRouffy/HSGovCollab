<?php

namespace Drupal\group_content_formatters\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;

/**
 * Plugin implementation of the 'plugin_reference_id' formatter.
 *
 * @FieldFormatter(
 *   id = "group_content_country_list",
 *   label = @Translation("Group content country."),
 *   field_types = {
 *     "group_content_item"
 *   }
 * )
 */
class GroupContentCountry extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode):array {
    $elements = [];

    // Initialize field.
    $items->setValue(1);
    $entity = $items->getEntity()->getTranslation($langcode);
    $global_content = $entity->global_content->getValue();

    if (isset($global_content[0]) && $global_content[0]['value'] == TRUE) {
      $elements[0][] = [
        '#type' => 'markup',
        '#markup' => $this->t('Global'),
      ];
    }
    else {
      foreach ($items as $delta => $item) {
        $value = $item->getValue();

        if (count($value['entity_gids']) > 1) {
          $elements[0][] = [
            '#type' => 'markup',
            '#markup' => $this->t('Multiple countries'),
          ];
        }
        else {
          /* @var GroupInterface $group */
          foreach (Group::loadMultiple($value['entity_gids']) as $group) {
            if ($group->hasField('field_group_status') && 'published' === $group->get('field_group_status')->value) {
              $elements[$delta][] = [
                '#type' => 'link',
                '#title' => $group->label(),
                '#url' => Url::fromRoute('entity.group.canonical', ['group' => $group->id()]),
              ];
            }
            else {
              $elements[$delta][] = [
                '#type' => 'markup',
                '#markup' => $group->label(),
              ];
            }
          }
        }
      }

    }

    return $elements;
  }

}
