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
 * Class GroupContentAlter.
 */
class GroupContentAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * @inheritdoc
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return 'views-exposed-form-group-content-embed-1' == $form['#id'];
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
          unset(
            $form['type']['#options']['news'],
            $form['type']['#options']['event'],
            $form['type']['#options']['document']
          );
          break;

        default:
          unset($form['type']['#options']['article']);
      }
    }
  }

}
