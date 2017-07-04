<?php

namespace Drupal\simplenews_customizations\Mail;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\simplenews\Mail\Mailer;
use Drupal\simplenews\Spool\SpoolStorageInterface;
use Drupal\user\Entity\User;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\State\StateInterface;
use Drupal\node\Entity\Node;
use Drupal\simplenews\Entity\Subscriber;
use Drupal\simplenews\NewsletterInterface;
use Drupal\simplenews\Mail\MailEntity;
use Drupal\simplenews\Mail\MailInterface;
use Drupal\simplenews\SkipMailException;
use Drupal\simplenews\SubscriberInterface;


/**
 *
 */
class MailerExtra extends Mailer {

  /**
   *
   */
  public function sendSpool($limit = SpoolStorageInterface::UNLIMITED, array $conditions = []) {
    $check_counter = 0;

    // Send pending messages from database cache.
    $spool = $this->spoolStorage->getMails($limit, $conditions);
    if (count($spool) > 0) {

      $super_user = User::load(1);
      $this->accountSwitcher->switchTo($super_user);

      $count_fail = $count_skipped = $count_success = 0;
      $sent = [];

      $this->startTimer();

      while ($mail = $spool->nextMail()) {
        $mail->setKey('node');
        $result = $this->sendMail($mail);

        // Update spool status.
        // This is not optimal for performance but prevents duplicate emails
        // in case of PHP execution time overrun.
        foreach ($spool->getProcessed() as $msid => $row) {
          $row_result = isset($row->result) ? $row->result : $result;
          $this->spoolStorage->updateMails([$msid], $row_result);
          if ($row_result['status'] == SpoolStorageInterface::STATUS_DONE) {
            $count_success++;
            if (!isset($sent[$row->entity_type][$row->entity_id][$row->langcode])) {
              $sent[$row->entity_type][$row->entity_id][$row->langcode] = 1;
            }
            else {
              $sent[$row->entity_type][$row->entity_id][$row->langcode]++;
            }
          }
          elseif ($row_result['status'] == SpoolStorageInterface::STATUS_SKIPPED) {
            $count_skipped++;
          }
          if ($row_result['error']) {
            $count_fail++;
          }
        }

        // Check every n emails if we exceed the limit.
        // When PHP maximum execution time is almost elapsed we interrupt
        // sending. The remainder will be sent during the next cron run.
        if (++$check_counter >= static::SEND_CHECK_INTERVAL && ini_get('max_execution_time') > 0) {
          $check_counter = 0;
          // Break the sending if a percentage of max execution time was exceeded.
          $elapsed = $this->getCurrentExecutionTime();
          if ($elapsed > static::SEND_TIME_LIMIT * ini_get('max_execution_time')) {
            $this->logger->warning('Sending interrupted: PHP maximum execution time almost exceeded. Remaining newsletters will be sent during the next cron run. If this warning occurs regularly you should reduce the !cron_throttle_setting.', [
              '!cron_throttle_setting' => \Drupal::l(t('Cron throttle setting'), new Url('simplenews.settings_mail')),
            ]);
            break;
          }
        }
      }

      // It is possible that all or at the end some results failed to get
      // prepared, report them separately.
      foreach ($spool->getProcessed() as $msid => $row) {
        $row_result = $row->result;
        $this->spoolStorage->updateMails([$msid], $row_result);
        if ($row_result['status'] == SpoolStorageInterface::STATUS_DONE) {
          $count_success++;
          if (isset($row->langcode)) {
            if (!isset($sent[$row->entity_type][$row->entity_id][$row->langcode])) {
              $sent[$row->entity_type][$row->entity_id][$row->langcode] = 1;
            }
            else {
              $sent[$row->entity_type][$row->entity_id][$row->langcode]++;
            }
          }
        }
        elseif ($row_result['status'] == SpoolStorageInterface::STATUS_SKIPPED) {
          $count_skipped++;
        }
        if ($row_result['error']) {
          $count_fail++;
        }
      }

      // Update subscriber count.
      if ($this->lock->acquire('simplenews_update_sent_count')) {
        foreach ($sent as $entity_type => $ids) {
          foreach ($ids as $entity_id => $languages) {
            \Drupal::entityManager()->getStorage($entity_type)->resetCache([$entity_id]);
            $entity = entity_load($entity_type, $entity_id);
            foreach ($languages as $langcode => $count) {
              $translation = $entity->getTranslation($langcode);
              $translation->simplenews_issue->sent_count = $translation->simplenews_issue->sent_count + $count;
            }
            $entity->save();
          }
        }
        $this->lock->release('simplenews_update_sent_count');
      }

      // Report sent result and elapsed time. On Windows systems getrusage() is
      // not implemented and hence no elapsed time is available.
      if (function_exists('getrusage')) {
        $this->logger->notice('%success emails sent in %sec seconds, %skipped skipped, %fail failed sending.', ['%success' => $count_success, '%sec' => round($this->getCurrentExecutionTime(), 1), '%skipped' => $count_skipped, '%fail' => $count_fail]);
      }
      else {
        $this->logger->notice('%success emails sent, %skipped skipped, %fail failed.', ['%success' => $count_success, '%skipped' => $count_skipped, '%fail' => $count_fail]);
      }

      $this->state->set('simplenews.last_cron', REQUEST_TIME);
      $this->state->set('simplenews.last_sent', $count_success);

      $this->accountSwitcher->switchBack();
      return $count_success;
    }
  }

  public function sendTest(NodeInterface $node, array $test_addresses) {
    // Force the current user to anonymous to ensure consistent permissions.
    $super_user = User::load(1);
    $this->accountSwitcher->switchTo($super_user);

    // Send the test newsletter to the test address(es) specified in the node.
    // Build array of test email addresses.

    // Send newsletter to test addresses.
    // Emails are send direct, not using the spool.
    $recipients = array('anonymous' => array(), 'user' => array());
    foreach ($test_addresses as $mail) {
      $mail = trim($mail);
      if (!empty($mail)) {
        $subscriber = simplenews_subscriber_load_by_mail($mail);
        if (!$subscriber) {
          // Create a stub subscriber. Use values from the user having the given
          // address, or if there is no such user, the anonymous user.
          if ($user = user_load_by_mail($mail)) {
            $subscriber = Subscriber::create()->fillFromAccount($user);
          }
          else {
            $subscriber = Subscriber::create(['mail' => $mail]);
          }
          // Keep the current language.
          $subscriber->setLangcode(\Drupal::languageManager()->getCurrentLanguage());
        }

        if ($subscriber->getUserId()) {
          $account = $subscriber->uid->entity;
          $recipients['user'][] = $account->getUserName() . ' <' . $mail . '>';
        }
        else {
          $recipients['anonymous'][] = $mail;
        }
        $mail = new MailEntity($node, $subscriber, \Drupal::service('simplenews.mail_cache'));
        $mail->setKey('test');
        $this->sendMail($mail);
      }
    }
    if (count($recipients['user'])) {
      $recipients_txt = implode(', ', $recipients['user']);
      drupal_set_message(t('Test newsletter sent to user %recipient.', array('%recipient' => $recipients_txt)));
    }
    if (count($recipients['anonymous'])) {
      $recipients_txt = implode(', ', $recipients['anonymous']);
      drupal_set_message(t('Test newsletter sent to anonymous %recipient.', array('%recipient' => $recipients_txt)));
    }

    $this->accountSwitcher->switchBack();
  }

}
