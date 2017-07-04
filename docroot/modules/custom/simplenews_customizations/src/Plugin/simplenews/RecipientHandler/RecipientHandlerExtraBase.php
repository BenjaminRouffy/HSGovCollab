<?php

namespace Drupal\simplenews_customizations\Plugin\simplenews\RecipientHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\simplenews\Plugin\simplenews\RecipientHandler\RecipientHandlerBase;
use Drupal\simplenews\SubscriberInterface;

/**
 * Base class for all Recipient Handler classes.
 *
 * This handler sends a newsletter issue to all subscribers of a given
 * newsletter.
 *
 * @RecipientHandler(
 *   id = "simplenews_all",
 *   title = @Translation("All newsletter subscribers"),
 *   types = {
 *     "default"
 *   }
 * )
 */
class RecipientHandlerExtraBase extends RecipientHandlerBase {

  /**
   * Implements SimplenewsRecipientHandlerInterface::buildRecipientQuery()
   */
  public function buildRecipientQuery($newsletter_id = 'default') {
    $select = db_select('simplenews_subscriber', 's');
    $select->innerJoin('simplenews_subscriber__subscriptions', 't', 's.id = t.entity_id');
    $select->addField('s', 'id', 'snid');
    $select->addField('s', 'mail');
    $select->addField('t', 'subscriptions_target_id', 'newsletter_id');
    if ($newsletter_id) {
      $select->condition('t.subscriptions_target_id', $newsletter_id);
    }
    $select->condition('t.subscriptions_status', SIMPLENEWS_SUBSCRIPTION_STATUS_SUBSCRIBED);
    $select->condition('s.status', SubscriberInterface::ACTIVE);

    $select->groupBy('s.id');
    $select->groupBy('s.mail');
    $select->groupBy('t.subscriptions_target_id');
    return $select;
  }

  /**
   *
   */
  public function settingsForm($element, $settings, $form_state) {
    // Add some text to describe the send situation.
    $subscriber_count = $this->count($this->settingsFormSubmit([], $form_state));
    $element['count'] = [
      '#type' => 'item',
      '#markup' => t('Send newsletter issue to @count subscribers.', ['@count' => $subscriber_count]),
      '#parents' => ['recipient_handler_settings'],
      '#prefix' => '<div id="recipient-handler-count">',
      '#suffix' => '</div>',
    ];
    return $element;
  }

  /**
   *
   */
  public static function settingsFormSubmit($settings, FormStateInterface $form_state) {
    $values = [];
    foreach (['type'] as $item) {
      if ($form_state->getValue($item)) {
        $values[$item] = $form_state->getValue($item);
      }
    }
    return $values;
  }

  /**
   * @TODO remane
   */
  public function ajaxUpdateRecipientHandlerSettings($form, FormStateInterface $form_state) {
    return empty($form['send']['recipient_handler_settings']['count']) ? [] : $form['send']['recipient_handler_settings']['count'];
  }

}
