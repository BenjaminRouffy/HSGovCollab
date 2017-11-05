<?php
/**
 * @file
 * MailCacheBuild class implementation.
 */

namespace Drupal\simplenews_customizations\Mail;

use Drupal\simplenews\Mail\MailCacheBuild as DefaultMailCacheBuild;
use Drupal\simplenews\Mail\MailInterface;

class MailCacheBuild extends DefaultMailCacheBuild {

  function isCacheable(MailInterface $mail, $group, $key) {
    // Only cache data and build information.
    return in_array($group, ['data', 'build']);
  }

}
