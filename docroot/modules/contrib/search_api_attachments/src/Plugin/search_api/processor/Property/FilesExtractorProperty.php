<?php

namespace Drupal\search_api_attachments\Plugin\search_api\processor\Property;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Processor\ConfigurablePropertyBase;
use Drupal\search_api\Processor\ConfigurablePropertyInterface;
use Drupal\search_api\Utility\Utility;
use Drupal\search_api_attachments\Plugin\search_api\processor\FilesExtrator;

/**
 * Defines an "files extractor" property.
 */
class FilesExtractorProperty extends ConfigurablePropertyBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'fields' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(FieldInterface $field, array $form, FormStateInterface $form_state) {
    $index = $field->getIndex();
    $configuration = $field->getConfiguration();

    $form['#attached']['library'][] = 'search_api/drupal.search_api.admin_css';
    $form['#tree'] = TRUE;

    $form['fields'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Extracted fields'),
      '#options' => [],
      '#attributes' => ['class' => ['search-api-checkboxes-list']],
      '#default_value' => $configuration['fields'],
      '#required' => TRUE,
    ];

    $fields = $index->getFields();
    /** @var \Drupal\search_api\Item\FieldInterface $field */
    foreach ($fields as $field) {
      $data_type = $field->getDataDefinition()->getDataType();
      if ($data_type != 'field_item:file') {
        continue;
      }

      $field_options[$field->getFieldIdentifier()] = $field->getPrefixedLabel();

      $form['fields'][$field->getFieldIdentifier()] = [
        '#description' => $field->getFieldIdentifier(),
      ];
    }

    // Set the field options in a way that sorts them first by whether they are
    // selected (to quickly see which one are included) and second by their
    // labels.
    asort($field_options, SORT_NATURAL);
    $selected = array_flip($configuration['fields']);
    $form['fields']['#options'] = array_intersect_key($field_options, $selected);
    $form['fields']['#options'] += array_diff_key($field_options, $selected);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(FieldInterface $field, array &$form, FormStateInterface $form_state) {
    $values = [
      'fields' => array_keys(array_filter($form_state->getValue('fields'))),
    ];
    $field->setConfiguration($values);
  }
}
