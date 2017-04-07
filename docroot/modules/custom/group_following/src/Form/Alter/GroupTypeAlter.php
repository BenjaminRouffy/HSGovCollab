<?php

namespace Drupal\group_following\Form\Alter;

use Drupal\Console\Command\Shared\TranslationTrait;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceValidateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupType;
use Drupal\user\Entity\User;

/**
 * dddd
 */
class GroupTypeAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {
  use StringTranslationTrait;

  protected $strings;

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

  /**
   * GroupTypeAlter constructor.
   */
  function __construct() {
    $this->strings = [
      'confirmation_popup_following_header' => $this->t('Confirmation popup following header'),
      'confirmation_popup_following_body' => $this->t('Confirmation popup following body'),
      'confirmation_popup_unfollowing_header' => $this->t('Confirmation popup unfollowing header'),
      'confirmation_popup_unfollowing_body' => $this->t('Confirmation popup unfollowing body'),
    ];
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

    $form['confirmation_popup_status'] = [
      '#type' => 'checkbox',
      '#title' => t('Confirmation popup status'),
      '#default_value' => $group_type->getThirdPartySetting('group_following', 'confirmation_popup_status', 0),
    ];

    foreach ($this->strings as $key => $value) {
      $form[$key] = [
        '#type' => 'textfield',
        '#title' => $value,
        '#default_value' => $group_type->getThirdPartySetting('group_following', $key, ''),
      ];
    }

    // Process the submitted values before they are stored.
    $form['#entity_builders'][] = [$this, 'setFollowingSettings'];
  }

  /**
   * Save settings
   */
  function setFollowingSettings($entity_type, GroupType $group, &$form, FormStateInterface $form_state) {
    $group->setThirdPartySetting('group_following', 'confirmation_popup_status', $form_state->getValue('confirmation_popup_status'));

    foreach ($this->strings as $key => $value) {
      $group->setThirdPartySetting('group_following', $key, $form_state->getValue($key));
    }
  }

}
