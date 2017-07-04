<?php

namespace Drupal\simplenews_customizations\Utility;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Url;
use Drupal\mimemail\Utility\MimeMailFormatHelper;

/**
 * Defines a class containing utility methods for formatting mime mail messages.
 */
class CustomMimeMailFormatHelper extends MimeMailFormatHelper {

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
    $content = preg_replace_callback($pattern, ['\Drupal\simplenews_customizations\Utility\CustomMimeMailFormatHelper', 'replaceFiles'], $html);

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

    $document = [[
      'Content-Type' => "text/html; charset=utf-8",
      'Content-Transfer-Encoding' => $encoding,
      'content' => $content,
    ],
    ];

    $files = static::mimeMailFile();

    return array_merge($document, $files);
  }

  /**
   * Helper function to format URLs.
   *
   * @param string $url
   *   The file path.
   * @param bool $to_embed
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
      $url = str_replace([' ', '+'], ['%20', '%2B'], $url);
      if (\Drupal::service('module_handler')->moduleExists('cdn')) {
        $url = preg_replace('!^' . base_path() . '!', $_SERVER['REQUEST_SCHEME'] . ':/', $url, 1);
      }
      else {
        $url = preg_replace('!^' . base_path() . '!', '', $url, 1);
      }

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
    $language = \Drupal::languageManager()->getDefaultLanguage();

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

    $options = [
      'query' => ($query) ? parse_url($query) : [],
      'fragment' => $fragment,
      'absolute' => TRUE,
      'language' => $language,
      'prefix' => $prefix,
    ];

    $url = Url::fromUserInput($path, $options)->toString();

    // If url() added a ?q= where there should not be one, remove it.
    if (preg_match('!^\?q=*!', $url)) {
      $url = preg_replace('!\?q=!', '', $url);
    }

    $url = str_replace('+', '%2B', $url);
    return $url;
  }

}
