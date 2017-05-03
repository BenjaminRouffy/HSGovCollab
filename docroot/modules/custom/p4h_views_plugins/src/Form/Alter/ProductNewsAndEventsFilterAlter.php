<?php

namespace Drupal\p4h_views_plugins\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;

/**
 * Class ProductNewsAndEventsFilterAlter.
 */
class ProductNewsAndEventsFilterAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return 'views-exposed-form-news-and-events-group-product-news-and-events' == $form['#id'];
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
    $form['#attributes']['data-accordion'] = $form['#id'];

    $form['wrap']['year'] = $form['year'];
    $form['wrap']['month'] = $form['month'];
    unset($form['year'], $form['month']);
  }

}
