<?php

namespace Drupal\group_customization\FormAlterService;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceValidateInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\Entity\User;

/**
 * dddd
 */
class GroupMembershipAlter implements FormAlterServiceSubmitInterface, FormAlterServiceBaseInterface {
  /**
   * @inheritdoc
   */
  public function formSubmit(&$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('entity_id') as $entity_id) {
      $entity = User::load($entity_id['target_id']);

      foreach ($form_state->getValue('group_roles') as $role) {
        switch ($role['target_id']) {
          case 'country-admin':
            $entity->addRole('country_managers');
            break;
          case 'project-admin':
            $entity->addRole('projects_managers');
            break;
        }
      }

      $entity->save();
    }
  }

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

}
