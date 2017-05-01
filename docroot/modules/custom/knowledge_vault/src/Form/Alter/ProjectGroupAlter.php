<?php

namespace Drupal\knowledge_vault\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceValidateInterface;

/**
 * Class DefaultUserEditAlter.
 */
class ProjectGroupAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface, FormAlterServiceValidateInterface {

  /**
   * Checks that form is matched to specific conditions.
   *
   * @return bool
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * Form alter custom implementation.
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    if (isset($form['field_based_on'])) {
      if (NULL == $form['field_based_on']['widget']['#default_value']) {
        $form['field_based_on']['widget']['#default_value'] = '_none';
      }

      if (isset($form['field_knowledge_vault'])) {
        $form['field_knowledge_vault']['widget'][0]['entity_gids'] += [
          '#states' => [
            'required' => [
              ':input[name="field_based_on"]' => ['value' => 'knowledge-vault'],
            ],
            'visible' => [
              ':input[name="field_based_on"]' => ['value' => 'knowledge-vault'],
            ],
          ],
        ];
      }

      if (isset($form['field_product'])) {
        $form['field_product']['widget'][0]['entity_gids'] += [
          '#states' => [
            'required' => [
              ':input[name="field_based_on"]' => ['value' => 'product'],
            ],
            'visible' => [
              ':input[name="field_based_on"]' => ['value' => 'product'],
            ],
          ],
        ];
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function formValidate(&$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if (isset($values['field_based_on'])) {
      if (empty($values['field_based_on'])) {
        foreach (['field_knowledge_vault‎', 'field_product'] as $field) {
          $value = $form_state->getValue($field);
          $value[0]['entity_gids'] = [];
          $form_state->setValue($field, $value);
        }

        return;
      }

      if ('knowledge-vault' == $values['field_based_on'][0]['value']) {
        $value = $form_state->getValue('field_product');
        $value[0]['entity_gids'] = [];
        $form_state->setValue('field_product', $value);
      }

      if ('product' == $values['field_based_on'][0]['value']) {
        $value = $form_state->getValue('field_knowledge_vault');
        $value[0]['entity_gids'] = [];
        $form_state->setValue('field_knowledge_vault', $value);
      }
    }
  }

}
