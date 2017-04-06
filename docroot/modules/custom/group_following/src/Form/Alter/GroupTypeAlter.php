<?php

namespace Drupal\group_following\Form\Alter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceValidateInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\Entity\User;

/**
 * dddd
 */
class GroupTypeAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {
  /**
   * @inheritdoc
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
    // Load the current node type configuration entity.
    $group_type = $form_state->getFormObject()->getEntity();

    $form['confirmation_popup_status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Confirmation popup status'),
      '#default_value' => $group_type->getThirdPartySetting('group_following', 'confirmation_popup_status', 0),
    );
  }
}
