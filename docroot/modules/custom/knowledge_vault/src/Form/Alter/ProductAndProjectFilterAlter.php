<?php

namespace Drupal\knowledge_vault\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;

/**
 * Class ProductAndProjectFilterAlter.
 */
class ProductAndProjectFilterAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return 'views-exposed-form-knowledge-vault-product-and-project' == $form['#id'];
  }

  /**
   * @inheritdoc
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    $form['wrap'] = [
      '#prefix' => '<div class="wrapper-filters">',
      '#suffix' => '</div>',
      '#weight' => -100,
    ];

    if (isset($form['child_type'])) {
      $form['child_type']['#options']['All'] = t('Select type');
      $form['wrap']['child_type'] = $form['child_type'];
      unset($form['child_type']);
    }
    $form['label']['#attributes']['placeholder'] = t('Search in products and projects');

    $form['label']['#prefix'] = '<div class="search-form-wrapper">';
    $form['actions']['#suffix'] = '</div>';

    $form['wrap']['actions'] = $form['actions'];
    unset($form['actions']);

    $form['wrap']['label'] = $form['label'];
    unset($form['label']);
  }

}
