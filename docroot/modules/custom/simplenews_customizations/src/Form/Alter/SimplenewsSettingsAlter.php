<?php

namespace Drupal\simplenews_customizations\Form\Alter;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;


/**
 * Class GroupContentAlter.
 */
class SimplenewsSettingsAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface, FormAlterServiceSubmitInterface {

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
    $options = [];
    $block_type = BlockContentType::load('member_icons');

    $result = $query = \Drupal::entityQuery('block_content')
      ->condition('type', 'member_icons')
      ->execute();

    /* @var BlockContent $block */
    foreach (BlockContent::loadMultiple($result) as $block) {
      $options[$block->id()] = $block->label();
    }

    $form['simplenews_default_options']['footer'] = [
      '#type' => 'select',
      '#title' => t('Select footer for mail'),
      '#options' => $options,
      '#default_value' => $block_type->getThirdPartySetting('simplenews_customizations', 'simplenews_footer', 0),
      '#required' => TRUE,
    ];
  }

  /**
   * @inheritdoc
   */
  public function formSubmit(&$form, FormStateInterface $form_state) {
    $block_type = BlockContentType::load('member_icons');

    $block_type->setThirdPartySetting('simplenews_customizations', 'simplenews_footer', $form_state->getValue('footer'));
    $block_type->save();
  }
}
