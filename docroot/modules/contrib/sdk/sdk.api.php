<?php

/**
 * @file
 * SDK API.
 */

/**
 * Define types of software development kits.
 *
 * @return array[]
 *   An associative array, keyed by machine name of SDK type and containing
 *   a list of the following values:
 *   - label<string>: human-readable label of SDK;
 *   - classes<string[]>:
 *     - form<string>: must be extended from "BaseForm".
 *     - deriver<string>: must be extended from "BaseDeriver".
 *
 * @see \Drupal\sdk\Api\Form\BaseForm
 * @see \Drupal\sdk\Api\Deriver\BaseDeriver
 */
function hook_sdk_types() {
  $types = [];

  $types['paypal'] = [
    'label' => t('PayPal'),
    'classes' => [
      'form' => PayPalForm::class,
      'deriver' => PayPalDeriver::class,
    ],
  ];

  return $types;
}
