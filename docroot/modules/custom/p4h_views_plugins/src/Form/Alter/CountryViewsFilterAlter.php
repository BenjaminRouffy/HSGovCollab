<?php

namespace Drupal\p4h_views_plugins\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;

/**
 * Class CountryViewsFilterAlter.
 */
class CountryViewsFilterAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return in_array($form['#id'], [
      'views-exposed-form-search-for-a-country-or-region-block-4',
      'views-exposed-form-search-for-a-country-or-region-block-1',
      'views-exposed-form-search-for-a-country-or-region-block-2',
    ]);
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

    $form['wrap']['actions'] = $form['actions'];
    unset($form['actions']);
    $form['wrap']['combine_1'] = $form['combine_1'];
    $form['wrap']['combine_1']['#title'] = $form['#info']['filter-combine_1']['label'];
    $form['wrap']['combine_1']['#attributes']['placeholder'] = t('Type in first letters or scroll down');
    unset($form['combine_1']);

    $form['wrap']['combine_1']['#prefix'] = '<div class="search-form-wrapper">';
    $form['wrap']['actions']['#suffix'] = '</div>';
  }

}
