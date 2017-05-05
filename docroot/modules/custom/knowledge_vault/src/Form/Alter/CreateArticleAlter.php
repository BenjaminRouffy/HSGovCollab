<?php

namespace Drupal\knowledge_vault\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;

/**
 * Class CreateArticleAlter.
 */
class CreateArticleAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    $group = \Drupal::routeMatch()->getParameter('group');

    if (!empty($group) && isset($form['field_knowledge_vault'])) {
      unset($form['field_knowledge_vault']);
    }
  }

}
