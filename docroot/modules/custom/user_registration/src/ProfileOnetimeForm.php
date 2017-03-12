<?php

namespace Drupal\user_registration;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\ProfileForm;

/**
 * Class ModuleHandlerAlterRegister.
 *
 * @package Drupal\module_handler_alter
 */
class ProfileOnetimeForm extends ProfileForm {
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['account']['name']['#access'] = FALSE;
    $form['account']['mail']['#access'] = FALSE;
    $form['account']['roles']['#access'] = FALSE;
    $form['account']['status']['#access'] = FALSE;
    $form['account']['notify']['#access'] = FALSE;

    $block = BlockContent::load(5);
    $form['info_block'] = \Drupal::entityManager()
      ->getViewBuilder('block_content')
      ->view($block);

    // The user may only change their own password without their current
    // password if they logged in via a one-time login link.
    if (!$form_state->get('user_pass_reset')) {
      $form['account']['current_pass']['#description'] = $this->t('Required if you want to change the %pass below. <a href=":request_new_url" title="Send password reset instructions via email.">Reset your password</a>.', array(
        '%pass' => $this->t('Password'),
        ':request_new_url' => $this->url('user.pass'),
      ));
    }
    return $form;
  }

  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirect('entity.user.canonical', array(
      'user' => $this->getEntity()
        ->id()
    ));
  }

}
