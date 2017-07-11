<?php

namespace Drupal\bricks_customizations\Plugin\Field\FieldWidget;

use Drupal\bricks_inline\Plugin\Field\FieldWidget\BricksTreeInlineWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'bricks_tree_inline_depth' widget.
 *
 * @FieldWidget(
 *   id = "bricks_tree_inline_depth",
 *   label = @Translation("Bricks tree (Inline entity form + depth)"),
 *   field_types = {
 *     "bricks",
 *     "bricks_revisioned"
 *   },
 *   multiple_values = true
 * )
 */
class BricksTreeInlineWithDepthWidget extends BricksTreeInlineWidget {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['level'] = [
      '#type' => 'textfield',
      '#size' => 10,
      '#title' => $this->t('Max depth level'),
      '#description' => $this->t('Default: 0 (unlimited)'),
      '#default_value' => $this->getSetting('level'),
    ];
    $form['hidden_view_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide view mode'),
      '#default_value' => $this->getSetting('hidden_view_mode'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $entities = $form_state->get(['inline_entity_form', $this->getIefId(), 'entities']);
    foreach ($entities as $delta => $value) {
      $this->formElementAlter($element['entities'][$delta], $items[$delta], $value['entity']);
    }

    $element['entities']['#level'] = $this->getSetting('level');
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'level' => 0,
      'hidden_view_mode' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Max depth: @depth', ['@depth' => $this->getSetting('level')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  private function formElementAlter(&$element, $item, $value) {
    if ($this->getSetting('hidden_view_mode')) {
      // @see _bricks_nest_items()
      unset($element['options']['view_mode']);
    }
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values =  parent::massageFormValues($values, $form, $form_state);
    foreach ($values as &$value) {
      // @TODO Sorry for hard coded display mode.
      $value['options']['view_mode'] = ($value['depth'] == 0 ? 'email_html' : 'email_html_two');
    }
    return $values;
  }

}
