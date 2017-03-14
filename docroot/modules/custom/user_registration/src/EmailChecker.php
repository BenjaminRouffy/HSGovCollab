<?php

namespace Drupal\user_registration;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Determines is email consisting in approved email domains list.
 */
class EmailChecker {

  /** @var ImmutableConfig */
  protected $userRegistrationSettings;

  /** @var string */
  protected $approvedEmails;

  /**
   * EmailChecker constructor.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->userRegistrationSettings = $config->get('user_registration.settings');
    $this->approvedEmails = $this->userRegistrationSettings->get('pattern');
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
    // Trim all non-word symbols.
    $quoted_emails = preg_quote(trim($this->approvedEmails), '@');
    $array_emails = preg_split('/\r\n?|\n/', $quoted_emails);
    // Remove empty values.
    $approved_emails = array_filter($array_emails);
    $pattern = '/(' . implode("|", $approved_emails) . ')$/is';

    // Mail is valid.
    if (!preg_match($pattern, $email)) {
      return FALSE;
    }

    return TRUE;
  }

}
