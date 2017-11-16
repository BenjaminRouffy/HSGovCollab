<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\ManyToOne;

/**
 * Custom Filter handler for node author.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("content_author_name")
 */
class ContentAuthor extends ManyToOne {
  /**
   * Default settings.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['type'] = array('default' => 'textfield');

    return $options;
  }

  /**
   * Mark form as extra setting form.
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * Extra settings form.
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $form['type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Selection Content type'),
      '#options' => $this->getContentTypes(),
      '#default_value' => $this->options['type'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function operators() {
    $operators = array(
      'in' => array(
        'title' => $this->t('Is one of'),
        'short' => $this->t('in'),
        'short_single' => $this->t('='),
        'method' => 'opSimple',
        'values' => 1,
      ),
    );

    return $operators;
  }

  /**
   * Add a type selector to the value form.
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    if ($exposed = $form_state->get('exposed')) {
      $form['value']['#type'] = 'select';
      $form['value']['#options'] = $this->selectOptions();
      $form['value']['#empty_option'] = $this->t('Select author');
      $form['value']['#empty_value'] = 'All';
      $form['value']['#size'] = 1;

    }
  }

  /**
   * Helper select value filter.
   */
  protected function selectOptions() {
    $options = [];
    $query = \Drupal::database()->select('node_field_data', 'node')
      ->condition('type', $this->options['type'])
      ->condition('status', 1)
      ->fields('node', ['uid'])
      ->execute();
    $authors_id_list = array_unique($query->fetchCol());
    $authors = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($authors_id_list);

    foreach ($authors as $author) {
      $options[$author->id()] = $author->field_first_name->value . ' ' . $author->field_last_name->value;
    }
    asort($options);

    return $options;
  }

  /**
   * Helper Content types option.
   */
  protected function getContentTypes() {
    $node_types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
    $options = [];

    foreach ($node_types as $node_type) {
      $options[$node_type->id()] = $node_type->label();
    }

    return $options;
  }
}
