<?php

namespace Drupal\p4h_svg_formatter;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;

/**
 * Class AbstractFetchManager.
 */
class AbstractFetchManager {

  /**
   * Drupal service implementation.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  public $fileSystem;

  /**
   * Drupal service implementation.
   *
   * @var bool|\Drupal\stage_file_proxy\FetchManagerInterface
   */
  public $fetchManager = FALSE;

  /**
   * Drupal service implementation.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  public $streamWrapperManager;

  /**
   * Drupal service implementation.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  public $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(FileSystemInterface $file_system, StreamWrapperManagerInterface $stream_wrapper_manager, ConfigFactoryInterface $config_factory) {
    if (\Drupal::hasService('stage_file_proxy.fetch_manager')) {
      $this->fetchManager = \Drupal::service('stage_file_proxy.fetch_manager');
    }
    $this->fileSystem = $file_system;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->configFactory = $config_factory;
  }

}
