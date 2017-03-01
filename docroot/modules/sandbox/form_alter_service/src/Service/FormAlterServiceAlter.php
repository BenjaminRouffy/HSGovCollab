<?php

namespace Drupal\form_alter_service\Service;

use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;

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
   *
   * @param FormAlterServiceAlterInterface $formAlterServiceAlter
   * @param $alias
   */
  public function addFormAlter(FormAlterServiceAlterInterface $formAlterServiceAlter, $alias) {
    $this->formAlters[$alias][] = $formAlterServiceAlter;
  }

  /**
   *
   * @param $alias
   * @return array
   */
  public function getFormAlter($alias) {
    if (array_key_exists($alias, $this->formAlters)) {
      return $this->formAlters[$alias];
    }
    return [];
  }

}
