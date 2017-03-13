<?php

namespace Drupal\mimemail\Utility;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Site\Settings;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Core\Url;

/**
 * Defines a class containing utility methods for formatting mime mail messages.
 */
class MimeMailFormatHelper {

  /**
   * Formats an address string.
   *
   * @todo Could use some enhancement and stress testing.
   *
   * @param mixed $address
   *   A user object, a text email address or an array containing name, mail.
   * @param boolean $simplify
   *   Determines if the address needs to be simplified. Defaults to FALSE.
   *
   * @return string
   *   A formatted address string or FALSE.
   */
  public static function mimeMailAddress($address, $simplify = FALSE) {
    if (is_array($address)) {
      // It's an array containing 'mail' and/or 'name'.
      if (isset($address['mail'])) {
        $output = '';
        if (empty($address['name']) || $simplify) {
          return $address['mail'];
        }
        else {
          return '"' . addslashes(Unicode::mimeHeaderEncode($address['name'])) . '" <' . $address['mail'] . '>';
        }
      }
      // It's an array of address items.
      $addresses = array();
      foreach ($address as $a) {
        $addresses[] = static::mimeMailAddress($a);
      }
      return $addresses;
    }

    // It's a user object.
    if (is_object($address) && isset($address->mail)) {
      if (empty($address->name) || $simplify) {
        return $address->mail;
      }
      else {
        return '"' . addslashes(Unicode::mimeHeaderEncode($address->name)) . '" <' . $address->mail . '>';
      }
    }

    // It's formatted or unformatted string.
    // @todo: shouldn't assume it's valid - should try to re-parse
    if (is_string($address)) {
      return $address;
    }

    return FALSE;
  }

  /**
   * Generate a multipart message body with a text alternative for some HTML text.
   *
   * @param string $body
   *   The HTML message body.
   * @param string $subject
   *   The message subject.
   * @param boolean $plain
   *   (optional) Whether the recipient prefers plaintext-only messages. Defaults to FALSE.
   * @param string $plaintext
   *   (optional) The plaintext message body.
   * @param array $attachments
   *   (optional) The files to be attached to the message.
   *
   * @return array
   *   An associative array containing the following elements:
   *   - body: A string containing the MIME-encoded multipart body of a mail.
   *   - headers: An array that includes some headers for the mail to be sent.
   *
   * The first mime part is a multipart/alternative containing mime-encoded sub-parts for
   * HTML and plaintext. Each subsequent part is the required image or attachment.
   */
  public static function mimeMailHtmlBody($body, $subject, $plain = FALSE, $plaintext = NULL, $attachments = array()) {
    if (empty($plaintext)) {
      // @todo Remove once filter_xss() can handle direct descendant selectors in inline CSS.
      // @see http://drupal.org/node/1116930
      // @see http://drupal.org/node/370903
      // Pull out the message body.
      preg_match('|<body.*?</body>|mis', $body, $matches);
      $plaintext = MailFormatHelper::htmlToText($matches[0]);
    }
    if ($plain) {
      // Plain mail without attachment.
      if (empty($attachments)) {
        $content_type = 'text/plain';
        return array(
          'body' => $plaintext,
          'headers' => array('Content-Type' => 'text/plain; charset=utf-8'),
        );
      }
      // Plain mail with attachement.
      else {
        $content_type = 'multipart/mixed';
        $parts = array(array(
          'content' => $plaintext,
          'Content-Type' => 'text/plain; charset=utf-8',
        ));
      }
    }
    else {
      $content_type = 'multipart/mixed';

      $plaintext_part = array('Content-Type' => 'text/plain; charset=utf-8', 'content' => $plaintext);

      // Expand all local links.
      $pattern = '/(<a[^>]+href=")([^"]*)/mi';
      $body = preg_replace_callback($pattern, ['\Drupal\mimemail\Utility\MimeMailFormatHelper', 'expandLinks'], $body);

      $mime_parts = static::mimeMailExtractFiles($body);

      $content = array($plaintext_part, array_shift($mime_parts));
      $content = static::mimeMailMultipartBody($content, 'multipart/alternative', TRUE);
      $parts = array(array('Content-Type' => $content['headers']['Content-Type'], 'content' => $content['body']));

      if ($mime_parts) {
        $parts = array_merge($parts, $mime_parts);
        $content = static::mimeMailMultipartBody($parts, 'multipart/related; type="multipart/alternative"', TRUE);
        $parts = array(array('Content-Type' => $content['headers']['Content-Type'], 'content' => $content['body']));
      }
    }

    if (is_array($attachments) && !empty($attachments)) {
      foreach ($attachments as $a) {
        $a = (object) $a;
        $path = isset($a->uri) ? $a->uri : (isset($a->filepath) ? $a->filepath : NULL);
        $content = isset($a->filecontent) ? $a->filecontent : NULL;
        $name = isset($a->filename) ? $a->filename : NULL;
        $type = isset($a->filemime) ? $a->filemime : NULL;
        static::mimeMailFile($path, $content, $name, $type, 'attachment');
        $parts = array_merge($parts, static::mimeMailFile());
      }
    }

    return static::mimeMailMultipartBody($parts, $content_type);
  }

  /**
   * Extracts links to local images from HTML documents.
   *
   * @param string $html
   *   A string containing the HTML source of the message.
   *
   * @return array
   *   An array containing the document body and the extracted files like the following.
   *     array(
   *       array(
   *         'name' => document name
   *         'content' => html text, local image urls replaced by Content-IDs,
   *         'Content-Type' => 'text/html; charset=utf-8')
   *       array(
   *         'name' => file name,
   *         'file' => reference to local file,
   *         'Content-ID' => generated Content-ID,
   *         'Content-Type' => derived using mime_content_type if available, educated guess otherwise
   *        )
   *     )
   */
  public static function mimeMailExtractFiles($html) {
    $pattern = '/(<link[^>]+href=[\'"]?|<object[^>]+codebase=[\'"]?|@import |[\s]src=[\'"]?)([^\'>"]+)([\'"]?)/mis';
    $content = preg_replace_callback($pattern, ['\Drupal\mimemail\Utility\MimeMailFormatHelper', 'replaceFiles'], $html);

    $encoding = '8Bit';
    $body = explode("\n", $content);
    foreach ($body as $line) {
      if (Unicode::strlen($line) > 998) {
        $encoding = 'base64';
        break;
      }
    }
    if ($encoding == 'base64') {
      $content = rtrim(chunk_split(base64_encode($content)));
    }

    $document = array(array(
      'Content-Type' => "text/html; charset=utf-8",
      'Content-Transfer-Encoding' => $encoding,
      'content' => $content,
    ));

    $files = static::mimeMailFile();

    return array_merge($document, $files);
  }

  /**
   * Helper function to extract local files.
   *
   * @param string $url
   *   (optional) The URI or the absolute URL to the file.
   * @param string $content
   *   (optional) The actual file content.
   * @param string $name
   *   (optional) The file name.
   * @param string $type
   *   (optional) The file type.
   * @param string $disposition
   *   (optional) The content disposition. Defaults to inline.
   *
   * @return
   *   The Content-ID and/or an array of the files on success or the URL on failure.
   */
  public static function mimeMailFile($url = NULL, $content = NULL, $name = '', $type = '', $disposition = 'inline') {
    static $files = array();
    static $ids = array();

    if ($url) {
      $image = preg_match('!\.(png|gif|jpg|jpeg)$!i', $url);
      $linkonly = \Drupal::config('mimemail.settings')->get('linkonly');
      // The file exists on the server as-is. Allows for non-web-accessible files.
      if (@is_file($url) && $image && !$linkonly) {
        $file = $url;
      }
      else {
        $url = static::mimeMailUrl($url, 'TRUE');
        // The $url is absolute, we're done here.
        $scheme = file_uri_scheme($url);
        if ($scheme == 'http' || $scheme == 'https' || preg_match('!mailto:!', $url)) {
          return $url;
        }
        // The $url is a non-local URI that needs to be converted to a URL.
        else {
          $file = (\Drupal::service('file_system')->realpath($url)) ? \Drupal::service('file_system')->realpath($url) : file_create_url($url);
        }
      }
    }
    // We have the actual content.
    elseif ($content) {
      $file = $content;
    }

    if (isset($file) && (@is_file($file) || $content)) {
      $public_path = \Drupal::config('system.file')->get('default_scheme') . '://';
      $no_access = !\Drupal::currentUser()->hasPermission('send arbitrary files');
      $not_in_public_path = strpos(\Drupal::service('file_system')->realpath($file), \Drupal::service('file_system')->realpath($public_path)) !== 0;
      if (@is_file($file) && $not_in_public_path && $no_access) {
        return $url;
      }

      if (!$name) {
        $name = (@is_file($file)) ? basename($file) : 'attachment.dat';
      }
      if (!$type) {
        $type = ($name) ? \Drupal::service('file.mime_type.guesser')->guess($name) : \Drupal::service('file.mime_type.guesser')->guess($file);
      }

      $id = md5($file) .'@'. $_SERVER['HTTP_HOST'];

      // Prevent duplicate items.
      if (isset($ids[$id])) {
        return 'cid:'. $ids[$id];
      }

      $new_file = array(
        'name' => $name,
        'file' => $file,
        'Content-ID' => $id,
        'Content-Disposition' => $disposition,
        'Content-Type' => $type,
      );

      $files[] = $new_file;
      $ids[$id] = $id;

      return 'cid:' . $id;
    }
    // The $file does not exist and no $content, return the $url if possible.
    elseif ($url) {
      return $url;
    }

    $ret = $files;
    $files = array();
    $ids = array();

    return $ret;
  }

  /**
   * Helper function to format URLs.
   *
   * @param string $url
   *   The file path.
   * @param boolean $to_embed
   *   (optional) Wheter the URL is used to embed the file. Defaults to NULL.
   *
   * @return string
   *   A processed URL.
   */
  public static function mimeMailUrl($url, $to_embed = NULL) {
    $url = urldecode($url);

    $to_link = \Drupal::config('mimemail.settings')->get('linkonly');
    $is_image = preg_match('!\.(png|gif|jpg|jpeg)!i', $url);
    $is_absolute = \Drupal::service('file_system')->uriScheme($url) != FALSE || preg_match('!(mailto|callto|tel)\:!', $url);

    if (!$to_embed) {
      if ($is_absolute) {
        return str_replace(' ', '%20', $url);
      }
    }
    else {
      $url = preg_replace('!^' . base_path() . '!', '', $url, 1);
      if ($is_image) {
        if ($to_link) {
          // Exclude images from embedding if needed.
          $url = file_create_url($url);
          $url = str_replace(' ', '%20', $url);
        }
        else {
          // Remove security token from URL, this allows for styled image embedding.
          // @see https://drupal.org/drupal-7.20-release-notes
          $url = preg_replace('/\\?itok=.*$/', '', $url);
        }

      }
      return $url;
    }

    $url = str_replace('?q=', '', $url);
    @list($url, $fragment) = explode('#', $url, 2);
    @list($path, $query) = explode('?', $url, 2);

    // If we're dealing with an intra-document reference, return it.
    if (empty($path)) {
      return '#' . $fragment;
    }

    // Get a list of enabled languages.
    $languages = \Drupal::languageManager()->getLanguages('enabled');
    $languages = $languages[1];

    // Default language settings.
    $prefix = '';
    $language =  \Drupal::languageManager()->getDefaultLanguage();

    // Check for language prefix.
    $args = explode('/', $path);
    foreach ($languages as $lang) {
      if ($args[0] == $lang->prefix) {
        $prefix = array_shift($args);
        $language = $lang;
        $path = implode('/', $args);
        break;
      }
    }

    $options = array(
      'query' => ($query) ? parse_url($query) : array(),
      'fragment' => $fragment,
      'absolute' => TRUE,
      'language' => $language,
      'prefix' => $prefix,
    );

    $url = Url::fromUserInput($path, $options)->toString();

    // If url() added a ?q= where there should not be one, remove it.
    if (preg_match('!^\?q=*!', $url)) {
      $url = preg_replace('!\?q=!', '', $url);
    }

    $url = str_replace('+', '%2B', $url);
    return $url;
  }

  /**
   * Build a multipart body.
   *
   * @param array $parts
   *   An associative array containing the parts to be included:
   *   - name: A string containing the name of the attachment.
   *   - content: A string containing textual content.
   *   - file: A string containing file content.
   *   - Content-Type: A string containing the content type of either file or content. Mandatory
   *     for content, optional for file. If not present, it will be derived from file the file if
   *     mime_content_type is available. If not, application/octet-stream is used.
   *   - Content-Disposition: (optional) A string containing the disposition. Defaults to inline.
   *   - Content-Transfer-Encoding: (optional) Base64 is assumed for files, 8bit for other content.
   *   - Content-ID: (optional) for in-mail references to attachements.
   *   Name is mandatory, one of content and file is required, they are mutually exclusive.
   * @param string $content_type
   *   (optional) A string containing the content-type for the combined message. Defaults to
   *   multipart/mixed.
   *
   * @return array
   *   An associative array containing the following elements:
   *   - body: A string containing the MIME-encoded multipart body of a mail.
   *   - headers: An array that includes some headers for the mail to be sent.
   */
  public static function mimeMailMultipartBody($parts, $content_type = 'multipart/mixed; charset=utf-8', $sub_part = FALSE) {
    // Control variable to avoid boundary collision.
    static $part_num = 0;

    $boundary = sha1(uniqid($_SERVER['REQUEST_TIME'], TRUE)) . $part_num++;
    $body = '';
    $headers = array(
      'Content-Type' => "$content_type; boundary=\"$boundary\"",
    );
    if (!$sub_part) {
      $headers['MIME-Version'] = '1.0';
      $body = "This is a multi-part message in MIME format.\n";
    }

    foreach ($parts as $part) {
      $part_headers = array();

      if (isset($part['Content-ID'])) {
        $part_headers['Content-ID'] = '<' . $part['Content-ID'] . '>';
      }

      if (isset($part['Content-Type'])) {
        $part_headers['Content-Type'] = $part['Content-Type'];
      }

      if (isset($part['Content-Disposition'])) {
        $part_headers['Content-Disposition'] = $part['Content-Disposition'];
      }
      elseif (strpos($part['Content-Type'], 'multipart/alternative') === FALSE) {
        $part_headers['Content-Disposition'] = 'inline';
      }

      if (isset($part['Content-Transfer-Encoding'])) {
        $part_headers['Content-Transfer-Encoding'] = $part['Content-Transfer-Encoding'];
      }

      // Mail content provided as a string.
      if (isset($part['content']) && $part['content']) {
        if (!isset($part['Content-Transfer-Encoding'])) {
          $part_headers['Content-Transfer-Encoding'] = '8bit';
        }
        $part_body = $part['content'];
        if (isset($part['name'])) {
          $part_headers['Content-Type'] .= '; name="' . $part['name'] . '"';
          $part_headers['Content-Disposition'] .= '; filename="' . $part['name'] . '"';
        }

        // Mail content references in a filename.
      }
      else {
        if (!isset($part['Content-Transfer-Encoding'])) {
          $part_headers['Content-Transfer-Encoding'] = 'base64';
        }

        if (!isset($part['Content-Type'])) {
          $part['Content-Type'] = \Drupal::service('file.mime_type.guesser')->guess($part['file']);
        }

        if (isset($part['name'])) {
          $part_headers['Content-Type'] .= '; name="' . $part['name'] . '"';
          $part_headers['Content-Disposition'] .= '; filename="' . $part['name'] . '"';
        }

        if (isset($part['file'])) {
          $file = (is_file($part['file'])) ? file_get_contents($part['file']) : $part['file'];
          $part_body = chunk_split(base64_encode($file), 76, Settings::get('mail_line_endings', PHP_EOL));

        }
      }

      $body .= "\n--$boundary\n";
      $body .= static::mimeMailRfcHeaders($part_headers) . "\n\n";
      $body .= isset($part_body) ? $part_body : '';
    }
    $body .= "\n--$boundary--\n";

    return array('headers' => $headers, 'body' => $body);
  }

  /**
   * Attempts to RFC822-compliant headers for the mail message or its MIME parts.
   *
   * @todo Could use some enhancement and stress testing.
   *
   * @param array $headers
   *   An array of headers.
   *
   * @return string
   *   A string containing the headers.
   */
  public static function mimeMailRfcHeaders($headers) {
    $header = '';
    $crlf = Settings::get('mail_line_endings', PHP_EOL);
    foreach ($headers as $key => $value) {
      $key = trim($key);
      // Collapse spaces and get rid of newline characters.
      $value = preg_replace('/(\s+|\n|\r|^\s|\s$)/', ' ', $value);
      // Fold headers if they're too long.
      // A CRLF may be inserted before any WSP.
      // @see http://tools.ietf.org/html/rfc2822#section-2.2.3
      if (Unicode::strlen($value) > 60) {
        // If there's a semicolon, use that to separate.
        if (count($array = preg_split('/;\s*/', $value)) > 1) {
          $value = trim(join(";$crlf ", $array));
        }
        else {
          $value = wordwrap($value, 50, "$crlf ", FALSE);
        }
      }
      $header .= $key . ": " . $value . $crlf;
    }
    return trim($header);
  }

  /**
   * Gives useful defaults for standard email headers.
   *
   * @param array $headers
   *   Message headers.
   * @param string $from
   *   The address of the sender.
   *
   * @return array
   *   Overwrited headers.
   */
  public static function mimeMailHeaders($headers, $from = NULL) {
    $default_from = \Drupal::config('system.site')->get('mail');

    // Overwrite standard headers.
    if ($from) {
      if (!isset($headers['From']) || $headers['From'] == $default_from) {
        $headers['From'] = $from;
      }
      if (!isset($headers['Sender']) || $headers['Sender'] == $default_from) {
        $headers['Sender'] = $from;
      }
      // This may not work. The MTA may rewrite the Return-Path.
      if (!isset($headers['Return-Path']) || $headers['Return-Path'] == $default_from) {
        if (preg_match('/[a-z\d\-\.\+_]+@(?:[a-z\d\-]+\.)+[a-z\d]{2,4}/i', $from, $matches)) {
          $headers['Return-Path'] = "<$matches[0]>";
        }
      }
    }

    // Convert From header if it is an array.
    if (is_array($headers['From'])) {
      $headers['From'] = static::mimeMailAddress($headers['From']);
    }

    // Run all headers through mime_header_encode() to convert non-ascii
    // characters to an rfc compliant string, similar to drupal_mail().
    foreach ($headers as $key => $value) {
      $headers[$key] = Unicode::mimeHeaderEncode($value);
    }

    return $headers;
  }

  /**
   * @param $matches
   * @return string
   */
  public static function expandLinks($matches) {
    return $matches[1] . self::mimeMailUrl($matches[2]);
  }

  public static function replaceFiles($matches) {
    return stripslashes($matches[1]) .self::mimeMailFile($matches[2]) . stripslashes($matches[3]);
  }

}
