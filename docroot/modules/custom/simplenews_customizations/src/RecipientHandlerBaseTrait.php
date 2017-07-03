<?php

namespace Drupal\simplenews_customizations;

use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
trait RecipientHandlerBaseTrait {

  /**
   *
   */
  public function buildRecipientCountQuery($settings = NULL) {
    return $this->buildRecipientQuery($settings)->countQuery();
  }

  /**
   *
   */
  public function count($settings = NULL) {
    return $this->buildRecipientCountQuery($settings)->execute()->fetchField();
  }

  /**
   * @TODO move
   */
  public function ajaxUpdateRecipientHandlerSettings($form, FormStateInterface $form_state) {
    return empty($form['send']['recipient_handler_settings']['count']) ? [] : $form['send']['recipient_handler_settings']['count'];
  }

}
