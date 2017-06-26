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
use Drupal\group\Entity\Group;
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
      /* @var User $user */
      $user = User::load($entity_id['target_id']);
      $roles = array_column($form_state->getValue('group_roles'), 'target_id');

      /* @var Group $group */
      $group = $form_state->getFormObject()->getEntity()->getGroup();

      switch ($group->getGroupType()->id()) {
        case 'country':
          $user->{in_array('country-admin', $roles) ? 'addRole' : 'removeRole'}('country_managers');
          break;
        case 'project':
          $user->{in_array('project-admin', $roles) ? 'addRole' : 'removeRole'}('projects_managers');
          break;
        case 'product':
          $user->{in_array('product-admin', $roles) ? 'addRole' : 'removeRole'}('product_manager');
          break;
        case 'knowledge_vault':
          $user->{in_array('knowledge_vault-admin', $roles) ? 'addRole' : 'removeRole'}('knowledge_vault_manager');
          break;
        case 'governance_area':
          $user->{in_array('governance_area-manager', $roles) ? 'addRole' : 'removeRole'}('governance_group_users');
          break;
      }

      $user->save();
    }
  }

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return TRUE;
  }

}
