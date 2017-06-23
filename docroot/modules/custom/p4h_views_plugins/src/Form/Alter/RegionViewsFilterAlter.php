<?php

namespace Drupal\p4h_views_plugins\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;

/**
 * Class RegionViewsFilterAlter.
 */
class RegionViewsFilterAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return in_array($form['#id'], [
      'views-exposed-form-search-for-a-country-or-region-block-3',
      'views-exposed-form-search-for-a-country-or-region-block-5',
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
    $form['wrap']['combine_2'] = $form['combine_2'];
    $form['wrap']['combine_2']['#title'] = $form['#info']['filter-combine_1']['label'];
    unset($form['combine_2']);

    $form['wrap']['combine_2']['#prefix'] = '<div class="search-form-wrapper">';
    $form['wrap']['actions']['#suffix'] = '</div>';
  }

}
