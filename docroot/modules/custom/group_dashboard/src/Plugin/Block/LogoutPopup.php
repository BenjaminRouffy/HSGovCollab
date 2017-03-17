<?php

namespace Drupal\group_dashboard\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a block for popup.
 *
 * @Block(
 *   id = "logout_popup",
 *   admin_label = @Translation("Logout popup"),
 * )
 */
class LogoutPopup extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'header' => '',
      'message' => '',
      'continue' => $this->t('Continue'),
      'cancel' => $this->t('Cancel'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#default_value' => $this->configuration['header'],
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#default_value' => $this->configuration['message'],
      '#required' => TRUE,
    ];

    $form['continue'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for "Yes" button'),
      '#default_value' => $this->configuration['continue'],
    ];

    $form['cancel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for "No" button'),
      '#default_value' => $this->configuration['cancel'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('header', $form_state->getValue('header'));
    $this->setConfigurationValue('message', $form_state->getValue('message'));
    $this->setConfigurationValue('continue', $form_state->getValue('continue'));
    $this->setConfigurationValue('cancel', $form_state->getValue('cancel'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['popup'] = [
      '#theme' => 'block__logout_popup',
    ];

    return $build;
  }

}
