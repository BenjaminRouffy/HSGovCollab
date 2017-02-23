<?php

namespace Drupal\sdk\Api\Form;

// Core components.
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
// SDK API components.
use Drupal\sdk\Api\Api;
use Drupal\sdk\Api\ExternalLink;

/**
 * Class BaseForm.
 */
abstract class BaseForm extends Api {

  use ExternalLink;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  abstract public function form(array $form, FormStateInterface $form_state);

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $form, FormStateInterface $form_state) {
  }

}
