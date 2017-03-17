<?php

namespace Drupal\form_alter_service\Interfaces;

use Drupal\Core\Form\FormStateInterface;

interface FormAlterServiceSubmitInterface {

  /**
   * Form submit custom implementation.
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formSubmit(&$form, FormStateInterface $form_state);

}
