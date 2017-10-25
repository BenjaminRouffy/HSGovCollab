<?php

namespace Drupal\migrate_social_autopost_implementation\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceValidateInterface;

/**
 * Class NodeEditCheckAutopostAlter.
 */
class NodeEditCheckAutopostAlter implements FormAlterServiceBaseInterface, FormAlterServiceValidateInterface {

  /**
   * Checks that form is matched to specific conditions.
   *
   * @return bool
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  public function formValidate(&$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if (!empty($values['field_social_autopost']) && empty($values['public_content']['value'])) {
      $form_state->setError($form['field_social_autopost'], t('In order to share the post to social media you have to click on a checkbox “Public content”.'));
    }
  }

}
