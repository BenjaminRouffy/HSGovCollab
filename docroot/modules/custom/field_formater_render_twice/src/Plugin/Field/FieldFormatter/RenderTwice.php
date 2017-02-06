<?php

namespace Drupal\field_formater_render_twice\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Plugin implementation of the 'RenderTwice' formatter.
 *
 * @FieldFormatter(
 *   id = "render_twice",
 *   label = @Translation("Render entity (twice)"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class RenderTwice extends EntityReferenceRevisionsEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'view_mode_twice' => 'default',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['view_mode_twice'] = array(
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type')),
      '#title' => $this->t('View mode twice'),
      '#default_value' => $this->getSetting('view_mode_twice'),
      '#required' => TRUE,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $view_modes = $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type'));
    $view_mode = $this->getSetting('view_mode_twice');
    $summary[] = $this->t('Second node rendered as @mode', array('@mode' => isset($view_modes[$view_mode]) ? $view_modes[$view_mode] : $view_mode));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $elements_twice = [];
    $view_mode = $this->getSetting('view_mode_twice');

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering entity @entity_type @entity_id. Aborting rendering.', array('@entity_type' => $entity->getEntityTypeId(), '@entity_id' => $entity->id()));
        return $elements_twice + $elements;
      }
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
      $elements_twice[$delta] = $view_builder->view($entity, $view_mode, $entity->language()->getId());

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += array('resource' => $entity->toUrl()->toString());
      }
      $depth = 0;
    }

    return [
      [
        $elements,
      ],
      [
        $elements_twice,
      ]
    ];
  }
}
