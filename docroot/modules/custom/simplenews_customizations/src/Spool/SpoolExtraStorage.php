<?php

namespace Drupal\simplenews_customizations\Spool;

use Drupal\simplenews\Spool\SpoolStorage;

/**
 *
 */
class SpoolExtraStorage extends SpoolStorage {

  /**
   *
   */
  public function getMails($limit = SpoolStorage::UNLIMITED, $conditions = []) {

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
