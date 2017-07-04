<?php

namespace Drupal\simplenews_customizations\Spool;

use Drupal\simplenews\Spool\SpoolList;

/**
 *
 */
class SpoolExtraList extends SpoolList {

  /**
   *
   */
  public function __construct(array $mails) {
    parent::__construct($mails);
  }

}
