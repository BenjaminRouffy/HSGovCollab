<?php

namespace Drupal\knowledge_vault\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Provides settings for knowledge_vault module.
 */
class KnowledgeVaultConfigForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'knowledge_vault_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'knowledge_vault.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('knowledge_vault.settings');
    $options = [];

    foreach(\Drupal::entityTypeManager()->getStorage('group')->loadByProperties(['type' => 'knowledge_vault']) as $key => $group) {
      $options[$key] = $group->label();
    }

    $form['contribute_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Contribute category'),
      '#default_value' => $config->get('contribute_category'),
      '#options' => $options,
      '#multiple' => FALSE,
      '#chosen' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // @TODO Validate other form elements settings.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('knowledge_vault.settings')
      ->set('contribute_category', $form_state->getValue('contribute_category'))
     ->save();

    parent::submitForm($form, $form_state);
  }

}
