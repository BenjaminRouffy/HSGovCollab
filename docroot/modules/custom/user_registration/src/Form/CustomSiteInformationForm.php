<?php

namespace Drupal\user_registration\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Form\SiteInformationForm;

/**
 * Extends core Site information form in order to add Initial user setup page.
 */
class CustomSiteInformationForm extends SiteInformationForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_registration_custom_site_information';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $site_config = $this->config('system.site');

    $form['redirect_page'] = [
      '#type' => 'details',
      '#title' => t('Initial user setup redirect page'),
      '#open' => TRUE,
    ];
    $form['redirect_page']['init_user_redirect_page'] = [
      '#type' => 'textfield',
      '#title' => t('Default initial user setup redirect page'),
      '#default_value' => $site_config->get('page.init_user_redirect_page'),
      '#size' => 40,
      '#description' => t('User will be redirected on this page when submit "User initial setup" form.'),
      '#field_prefix' => $this->requestContext->getCompleteBaseUrl(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('system.site')
      ->set('page.init_user_redirect_page', $form_state->getValue('init_user_redirect_page'))
      ->save();
  }

}
