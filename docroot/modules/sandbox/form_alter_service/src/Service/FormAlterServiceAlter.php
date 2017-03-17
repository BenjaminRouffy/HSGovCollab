<?php

namespace Drupal\form_alter_service\Service;

use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;

/**
 * Class FormAlterServiceAlter.
 */
class FormAlterServiceAlter {

  /**
   * @var array Form alter implementations.
   */
  private $formAlters;

  /**
   * FormAlterServiceAlter constructor.
   */
  public function __construct() {
    $this->formAlters = array();
  }

  /**
   * {@inheritdoc}
   */
  public function addFormAlter(FormAlterServiceBaseInterface $formAlterServiceAlter, $form_id) {
    $this->formAlters[$form_id][] = $formAlterServiceAlter;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormAlter($form_id) {
    if (array_key_exists($form_id, $this->formAlters)) {
      return $this->formAlters[$form_id];
    }
    return [];
  }

}
