<?php

namespace Drupal\form_alter_service\Interfaces;

use Drupal\Core\Form\FormStateInterface;

interface FormAlterServiceAlterInterface {

  /**
   * Form alter custom implementation.
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formAlter(&$form, FormStateInterface $form_state);

}
