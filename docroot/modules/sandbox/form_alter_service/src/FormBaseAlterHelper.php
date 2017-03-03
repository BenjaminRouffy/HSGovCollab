<?php

namespace Drupal\form_alter_service;

use Drupal\Component\Utility\NestedArray;

class FormBaseAlterHelper {

  public static function getCustomPubField(&$form, $string) {
    return NestedArray::getValue($form, [
      $string,
      'widget',
      'value',
      '#default_value'
    ]);
  }

  public function getField(&$form, $string) {
    return NestedArray::getValue($form, [
      $string,
      'widget',
      '#default_value'
    ]);
  }
  public function hasWidget(&$form, $string) {
    $key_exists = NULL;
    NestedArray::getValue($form, [
      $string,
      'widget',
    ], $key_exists);
    return $key_exists;
  }
}
