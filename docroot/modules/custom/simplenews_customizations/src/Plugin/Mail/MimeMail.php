<?php

namespace Drupal\simplenews_customizations\Plugin\Mail;

use Drupal\mimemail\Plugin\Mail\MimeMail as BaseMimeMail;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\simplenews_customizations\Utility\CustomMimeMailFormatHelper;

/**
 * Defines the default Drupal mail backend, using PHP's native mail() function.
 *
 * @Mail(
 *   id = "custom_mime_mail",
 *   label = @Translation("Mime Mail mailer (Custom)"),
 *   description = @Translation("Sends MIME-encoded emails with embedded images and attachments..")
 * )
 */
class MimeMail extends BaseMimeMail {

  /**
   * {@inheritdoc}
   */
  public function prepareMessage(array $message) {
    $module = $message['module'];
    $key = $message['key'];
    $to = $message['to'];
    $from = $message['from'];
    $subject = $message['subject'];
    $body = $message['body'];

    $headers = isset($message['params']['headers']) ? $message['params']['headers'] : [];
    $plain = isset($message['params']['plain']) ? $message['params']['plain'] : NULL;
    $plaintext = isset($message['params']['plaintext']) ? $message['params']['plaintext'] : NULL;
    $attachments = isset($message['params']['attachments']) ? $message['params']['attachments'] : [];

    $site_name = \Drupal::config('system.site')->get('name');
    // $site_mail = variable_get('site_mail', ini_get('sendmail_from'));.
    $site_mail = \Drupal::config('system.site')->get('mail');

    // Override site mails default sender when using default engine.
    if ((empty($from) || $from == $site_mail)) {
      $mimemail_name = \Drupal::config('mimemail.settings')->get('name');
      $mimemail_mail = \Drupal::config('mimemail.settings')->get('mail');
      $from = [
        'name' => !empty($mimemail_name) ? $mimemail_name : $site_name,
        'mail' => !empty($mimemail_mail) ? $mimemail_mail : $site_mail,
      ];
    }

    // Body is empty, this is a plaintext message.
    if (empty($body)) {
      $plain = TRUE;
    }
    // Try to determine recipient's text mail preference.
    elseif (is_null($plain)) {
      if (is_object($to) && isset($to->data['mimemail_textonly'])) {
        $plain = $to->data['mimemail_textonly'];
      }
      elseif (is_string($to) && \Drupal::service('email.validator')->isValid($to)) {
        if (is_object($account = user_load_by_mail($to)) && isset($account->data['mimemail_textonly'])) {
          $plain = $account->data['mimemail_textonly'];
          // Might as well pass the user object to the address function.
          $to = $account;
        }
      }
    }

    // Removing newline character introduced by _drupal_wrap_mail_line();
    $subject = str_replace(["\n"], '', trim(MailFormatHelper::htmlToText($subject)));

    $hook = [
      'mimemail_message__' . $key,
      'mimemail_message__' . $module . '__' . $key,
    ];

    $body = [
      '#theme' => 'mimemail_messages',
      '#module' => $module,
      '#key' => $key,
      '#recipient' => $to,
      '#subject' => $subject,
      '#body' => $body,
    ];

    $body = \Drupal::service('renderer')->renderRoot($body);
    $emogrifier = new \Pelago\Emogrifier($body);
    $body = $emogrifier->emogrify();

    $from = CustomMimeMailFormatHelper::mimeMailAddress($from);
    $mail = CustomMimeMailFormatHelper::mimeMailHtmlBody($body, $subject, $plain, $plaintext, $attachments);
    $headers = array_merge($message['headers'], $headers, $mail['headers']);

    // $message['to'] = MimeMailFormatHelper::mimeMailAddress($to, $simple_address);.
    $message['to'] = CustomMimeMailFormatHelper::mimeMailAddress($to);
    $message['from'] = $from;
    $message['subject'] = $subject;
    $message['body'] = $mail['body'];
    $message['headers'] = CustomMimeMailFormatHelper::mimeMailHeaders($headers, $from);

    return $message;
  }

}
