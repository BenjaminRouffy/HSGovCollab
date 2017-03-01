<?php

namespace Drupal\form_alter_service\Interfaces;

use Drupal\Core\Form\FormStateInterface;

interface FormAlterServiceAlterInterface {

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return mixed
   */
  public function formAlter(&$form, FormStateInterface $form_state);

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return mixed
   */
  public function formValidate(&$form, FormStateInterface $form_state);

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return mixed
   */
  public function formSubmit(&$form, FormStateInterface $form_state);

  /**
   * @return mixed
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id);

}
