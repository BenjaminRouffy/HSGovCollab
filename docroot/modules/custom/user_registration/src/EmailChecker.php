<?php

namespace Drupal\user_registration;

/**
 * Determines is email consisting in approved email domains list.
 */
class EmailChecker {

  protected $approvedEmails;

  /**
   * EmailChecker constructor.
   */
  public function __construct() {
    $this->approvedEmails = \Drupal::config('user_registration.settings')->get('pattern');
  }

  /**
   * Compare user's email with list of approved email domains.
   *
   * @param string $email
   *   User's email.
   *
   * @return bool
   *   TRUE if user's email has approved domain.
   */
  public function isApprove($email) {
    $approved_emails = preg_quote(trim($this->approvedEmails), '@');
    $approved_emails = str_replace(["\r\n", "\n", "\n\r"], "|", $approved_emails);
    $pattern = '/(' . $approved_emails . ')$/is';

    if (preg_match($pattern, $email) !== FALSE) {
      return TRUE;
    }

    return FALSE;
  }
}
