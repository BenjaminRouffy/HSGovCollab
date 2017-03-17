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
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['header'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#default_value' => isset($config['header']) ? $config['header'] : '',
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#default_value' => isset($config['message']) ? $config['message'] : '',
      '#required' => TRUE,
    ];

    $form['continue'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for "Yes" button'),
      '#default_value' => isset($config['continue']) ? $config['continue'] : 'Continue',
    ];

    $form['cancel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text for "No" button'),
      '#default_value' => isset($config['cancel']) ? $config['cancel'] : 'Cancel',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('header', $form_state->getValue('header'));
    $this->setConfigurationValue('message', $form_state->getValue('message'));
    $this->setConfigurationValue('continue', $form_state->getValue('continue', 'Continue'));
    $this->setConfigurationValue('cancel', $form_state->getValue('cancel', 'Cancel'));
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
