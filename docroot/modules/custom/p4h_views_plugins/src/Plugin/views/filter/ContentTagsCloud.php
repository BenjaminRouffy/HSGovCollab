<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\Views;

/**
 * Custom Filter handler for content tagadelic tags.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("content_tags_cloud")
 */
class ContentTagsCloud extends ManyToOne {
  /**
   * {@inheritdoc}
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['tags'] = ['default' => ''];
    $options['count'] = ['default' => 20];
    $options['view_selection'] = ['default' => ''];
    $options['view_display_selection'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $form['tags'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Tags style'),
      '#default_value' => $this->options['tags'],
    ];

    $form['count'] = [
      '#type' => 'number',
      '#title' => $this->t('Tags count'),
      '#default_value' => $this->options['count'],
      '#min' => 1,
      '#max' => 50,
      '#required' => TRUE,
    ];

    $form['view_selection'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select tagadelic view display'),
      '#options' => Views::getViewsAsOptions(FALSE, 'enabled'),
      '#default_value' => $this->options['view_selection'],
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);
    $options = [];

    $view_selection = explode(':', $this->options['view_selection']);

    $tags = views_get_view_result($view_selection[0], $view_selection[1]);

    if (count($tags) > $this->options['count']) {
      $rand = array_rand($tags, $this->options['count']);

      foreach ($rand as $index => $tag) {
        $rand[$index] = $tags[$tag];
      }

      $tags = $rand;
    }

    shuffle($tags);

    foreach ($tags as $tag) {
      $options[$tag->_entity->id()] = \Drupal::entityManager()
        ->getTranslationFromContext($tag->_entity)
        ->label();
    }

    if ($exposed = $form_state->get('exposed')) {
      $form['value']['#type'] = 'select';
      $form['value']['#options'] = $options;
      $form['value']['#empty_value'] = 'All';
      $form['value']['#size'] = 1;
    }

    if (!empty($this->options['tags'])) {
      $form['#tags_styles'][$this->field] = $tags;
    }

  }
}
