<?php

namespace Drupal\simplenews_customizations\Spool;

use Drupal\simplenews\Spool\SpoolList;
use Drupal\simplenews\SubscriberInterface;

/**
 *
 */
class SpoolExtraList extends SpoolList {

  /**
   *
   */
  public function __construct(array $mails) {
    parent::__construct($mails);
    foreach ($this->mails as $key => &$mail) {
      if (!$mail->data) {
        $mail->data = entity_create('simplenews_subscriber', [
          'id' => $mail->snid,
          'mail' => $mail->mail,
          'language' => \Drupal::languageManager()->getDefaultLanguage()->getId(),
          'status' => SubscriberInterface::ACTIVE,
          'uid' => \Drupal::currentUser()->getAccount()->id(),
        ]);
      }
    }
  }

}
