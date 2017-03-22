<?php

namespace Drupal\form_alter_service\Interfaces;

use Drupal\Core\Form\FormStateInterface;

interface FormAlterServiceBaseInterface {

  /**
   * Checks that form is matched to specific conditions.
   *
   * @return bool
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id);

}
