<?php

namespace Drupal\knowledge_vault\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;

/**
 * Class CreateArticleAlter.
 */
class CreateArticleAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {
  use StringTranslationTrait;

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
    else {
      $form['field_knowledge_vault']['widget'][0]['entity_gids']['#required'] = TRUE;
    }

    if (isset($form['title'])) {
      $form['title']['widget'][0]['value']['#attributes']['placeholder'] = $this->t('Enter title');
    }

    if (isset($form['field_author'])) {
      $form['field_author']['widget'][0]['value']['#attributes']['placeholder'] = $this->t('Enter your name');
    }

    if (isset($form['field_tags'])) {
      $form['field_tags']['widget']['target_id']['#attributes']['placeholder'] = $this->t('Enter tag (s),');
    }
  }

}
