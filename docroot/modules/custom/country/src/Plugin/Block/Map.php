<?php

namespace Drupal\country\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Map.
 *
 * @Block(
 *   id = "map",
 *   category = @Translation("Custom"),
 *   admin_label = @Translation("Map")
 * )
 */
class Map extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'key' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google API key'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['key'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    foreach ($this->defaultConfiguration() as $key => $default_value) {
      $this->configuration[$key] = $form_state->getValue($key);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return ['#theme' => 'block-map'];
  }

}
