<?php

namespace Drupal\group_following\Form;

use Drupal\ajax_test\Controller\AjaxTestController;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implements an group join form.
 */
class FollowConfigurationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'follow_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Group $group = NULL) {
    $form['following_popup_status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Following popup status.'),
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}
}
