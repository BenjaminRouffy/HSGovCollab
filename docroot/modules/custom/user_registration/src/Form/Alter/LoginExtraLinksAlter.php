<?php

namespace Drupal\user_registration\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceSubmitInterface;

/**
 * Class DefaultUserEditAlter.
 */
class LoginExtraLinksAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * Checks that form is matched to specific conditions.
   *
   * @return bool
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
    $form['user_links'] = array();
    if (\Drupal::config('user.settings')->get('register') != USER_REGISTER_ADMINISTRATORS_ONLY) {
      $form['user_links']['request_password'] = [
        '#type' => 'link',
        '#title' => t('Forgot password'),
        '#url' => Url::fromRoute('user.pass', [], [
          'attributes' => [
            'title' => t('Send password reset instructions via email.'),
            'class' => ['request-password-link'],
          ],
        ]),
      ];
      $form['user_links']['create_account'] = [
        '#type' => 'link',
        '#title' => t('Not a member yet? Sign up here'),
        '#url' => Url::fromRoute('page_manager.page_view_sign_up', [], [
          'attributes' => [
            'title' => t('Not a member yet? Sign up here'),
            'class' => ['create-account-link'],
          ],
        ]),
      ];
    }
  }

}
