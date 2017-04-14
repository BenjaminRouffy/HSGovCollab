<?php

namespace Drupal\friends\Plugin\WebformHandler;


use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;
use Drupal\webform\Annotation\WebformHandler;

use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;

/**
 * Emails a webform submission.
 *
 * @WebformHandler(
 *   id = "contact_to_user",
 *   label = @Translation("Contact to user"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a webform submission via an email."),
 *   cardinality = \Drupal\webform\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class ContactToUserWebformHandler extends EmailWebformHandler {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    if (isset($form['to']['to_mail'])) {
      unset($form['to']['to_mail']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function sendMessage(array $message) {
    // Send mail.
    /* @var UserInterface $user */
    $user = \Drupal::routeMatch()->getParameter('user');

    if (!empty($user)) {
      $message['to_mail'] = $user->getEmail();
      parent::sendMessage($message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function resendMessageForm(array $message) {
    $element = parent::resendMessageForm($message);

    if (isset($form['to']['to_mail'])) {
      unset($form['to']['to_mail']);
    }

    return $element;
  }

}
