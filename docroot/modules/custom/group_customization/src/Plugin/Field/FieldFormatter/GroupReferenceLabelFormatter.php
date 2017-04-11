<?php

namespace Drupal\group_customization\Plugin\Field\FieldFormatter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "group_reference_label",
 *   label = @Translation("Group label"),
 *   description = @Translation("Display the label of the referenced group."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class GroupReferenceLabelFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $account = \Drupal::currentUser();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      if ('group' !== $entity->getEntityTypeId()) {
        return parent::viewElements($items, $langcode);
      }

      $label = $this->t('Part of the @region Region', ['@region' => $entity->label()]);
      $access_to_link = AccessResult::allowedIfHasPermission($account, 'access group link');

      if ($access_to_link->isAllowed() && !$entity->isNew()) {
        $url = $entity->toUrl();

        $elements[$delta] = [
          '#type' => 'link',
          '#title' => $label,
          '#url' => $url,
          '#options' => $url->getOptions(),
        ];

        if (!empty($items[$delta]->_attributes)) {
          $elements[$delta]['#options'] += ['attributes' => []];
          $elements[$delta]['#options']['attributes'] += $items[$delta]->_attributes;
          // Unset field item attributes since they have been included in the
          // formatter output and shouldn't be rendered in the field template.
          unset($items[$delta]->_attributes);
        }
      }
      else {
        $elements[$delta] = [
          '#markup' => $label,
          '#prefix' => '<span>',
          '#suffix' => '</span>',
        ];
      }
      $elements[$delta]['#cache']['tags'] = $entity->getCacheTags();
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity) {
    $account = \Drupal::currentUser();

    return AccessResult::allowedIfHasPermission($account, 'access group label');
  }

}
