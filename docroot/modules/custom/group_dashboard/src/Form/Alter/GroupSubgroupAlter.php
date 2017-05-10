<?php

namespace Drupal\group_dashboard\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\group\Entity\GroupTypeInterface;

/**
 * Class GroupSubgroupAlter.
 */
class GroupSubgroupAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return 'views-exposed-form-subgroups-page-1' == $form['#id'];
  }

  /**
   * @inheritdoc
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    /* @var Group $group */
    $group = \Drupal::routeMatch()->getParameter('group');

    if (!empty($group)) {
      switch ($group->bundle()) {
        case 'knowledge_vault':
          unset($form['type']['#options']['country']);
          break;

        case 'product':
          unset(
            $form['type']['#options']['country'],
            $form['type']['#options']['product']
          );
          break;

        case 'country':
        case 'governance_area':
          unset(
            $form['type']['#options']['product'],
            $form['type']['#options']['country']
          );
          break;

        case 'region':
          unset($form['type']['#options']['product']);
          break;
      }
    }
  }

}
