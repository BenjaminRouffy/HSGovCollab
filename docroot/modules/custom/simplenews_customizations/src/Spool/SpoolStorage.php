<?php

namespace Drupal\simplenews_customizations\Spool;

use Drupal\simplenews\Spool\SpoolStorage as DefaultSpoolStorage;

/**
 *
 */
class SpoolStorage extends DefaultSpoolStorage {

  /**
   *
   */
  public function getMails($limit = DefaultSpoolStorage::UNLIMITED, $conditions = []) {

    $instance = parent::getMails($limit, $conditions);
    // Sadly ::getEntity() is protected at the moment.
    $function = function () {
      return $this->mails;
    };
    $function = \Closure::bind($function, $instance, get_class($instance));
    $mails = $function();
    return new SpoolExtraList($mails);
  }

}
