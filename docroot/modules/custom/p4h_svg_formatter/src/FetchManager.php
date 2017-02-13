<?php

namespace Drupal\p4h_svg_formatter;

/**
 * Class FetchManager.
 */
class FetchManager extends AbstractFetchManager {

  /**
   * Download a public file from stage.
   *
   * @param string $uri
   *   Drupal schema URI.
   *
   * @return false|null|string
   *   Full path to create file.
   */
  public function download($uri) {
    // Stage File proxy module is disabled or FetchManagerInterface service is not supported.
    if (empty($this->fetchManager)) {
      return FALSE;
    }

    $wrapper = $this->streamWrapperManager->getViaUri($uri);
    $relative_path = file_uri_target($uri);
    $server = $this->configFactory->get('stage_file_proxy.settings')
      ->get('origin');

    if ($server) {
      $origin_dir = $this->configFactory->get('stage_file_proxy.settings')
        ->get('origin_dir');
      $remote_file_dir = trim($origin_dir) ?: $wrapper->getDirectoryPath();
      $this->fetchManager->fetch($server, $remote_file_dir, $relative_path);
      return $this->fileSystem->realpath($uri);
    }

  }

}
