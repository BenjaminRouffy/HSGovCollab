<?php

namespace Drupal\form_alter_service\Interfaces;

use Drupal\Core\Form\FormStateInterface;

interface FormAlterServiceValidateInterface {

  /**
   * Form validation custom implementation.
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formValidate(&$form, FormStateInterface $form_state);

}
