<?php
/**
 * @file
 * Class FormBaseAlterHelper file.
 */
namespace Drupal\form_alter_service;

use Drupal\Component\Utility\NestedArray;

/**
 * Class FormBaseAlterHelper
 * @deprecated
 */
class FormBaseAlterHelper {

  /**
   * {@inheritdoc}
   */
  public static function getCustomPubField(&$form, $string) {
    return NestedArray::getValue($form, [
      $string,
      'widget',
      'value',
      '#default_value'
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getField(&$form, $string) {
    return NestedArray::getValue($form, [
      $string,
      'widget',
      '#default_value'
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function hasWidget(&$form, $string) {
    $key_exists = NULL;
    NestedArray::getValue($form, [
      $string,
      'widget',
    ], $key_exists);
    return $key_exists;
  }
}
