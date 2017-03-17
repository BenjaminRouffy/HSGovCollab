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

    $block = BlockContent::load(22);
    $form['info_block'] = \Drupal::entityManager()
      ->getViewBuilder('block_content')
      ->view($block);

    $form['info_block']['field_title']['#theme_wrappers'][] = 'good_to_know';
    $form['info_block']['#prefix'] = '<div class="content top-text-region">';
    $form['info_block']['#suffix'] = '</div>';

    // The user may only change their own password without their current
    // password if they logged in via a one-time login link.
    if (!$form_state->get('user_pass_reset')) {
      unset($form['account']['current_pass']['#description']);
    }
    return $form;
  }

  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    /*$form_state->setRedirect('entity.user.canonical', array(
      'user' => $this->getEntity()
        ->id()
    ));*/
    // @TODO Temporary redirect to front page.
    $form_state->setRedirect('<front>');

  }

}
