<?php

namespace Drupal\events\FormAlterService;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceValidateInterface;

class EventAlter implements FormAlterServiceAlterInterface, FormAlterServiceValidateInterface , FormAlterServiceBaseInterface {

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * Form alter custom implementation.
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    $element = &$form['custom_date_widget'];
  }

  /**
   * Form validation custom implementation.
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formValidate(&$form, FormStateInterface $form_state) {

  }

}
