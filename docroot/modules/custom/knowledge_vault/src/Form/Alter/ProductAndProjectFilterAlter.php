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
    $form['label']['#attributes']['placeholder'] = t('Search in products and projects');

    if (isset($form['type_1'])) {
      $form['type_1']['#options']['All'] = t('Select type');
    }
    $form['label']['#prefix'] = '<div class="search-form-wrapper">';
    $form['actions']['#suffix'] = '</div>';
  }

}
