<?php

namespace Drupal\simplenews_customizations\Mail;

use Drupal\Core\Url;
use Drupal\simplenews\Mail\Mailer;
use Drupal\simplenews\Spool\SpoolStorageInterface;
use Drupal\user\Entity\User;

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

}
