<?php

namespace Drupal\imagemagick\Plugin\FileMetadata;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\file_mdm\FileMetadataException;
use Drupal\file_mdm\Plugin\FileMetadata\FileMetadataPluginBase;
use Drupal\imagemagick\ImagemagickExecArguments;
use Drupal\imagemagick\ImagemagickExecManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * FileMetadata plugin for ImageMagick's identify results.
 *
 * @FileMetadata(
 *   id = "imagemagick_identify",
 *   title = @Translation("ImageMagick identify"),
 *   help = @Translation("File metadata plugin for ImageMagick identify results."),
 * )
 */
class ImagemagickIdentify extends FileMetadataPluginBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The ImageMagick execution manager service.
   *
   * @var \Drupal\imagemagick\ImagemagickExecManagerInterface
   */
  protected $execManager;

  /**
   * Constructs an ImagemagickIdentify plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_service
   *   The cache service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\imagemagick\ImagemagickExecManagerInterface $exec_manager
   *   The ImageMagick execution manager service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, CacheBackendInterface $cache_service, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, ImagemagickExecManagerInterface $exec_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $cache_service, $config_factory);
    $this->moduleHandler = $module_handler;
    $this->execManager = $exec_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.file_mdm'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('imagemagick.exec_manager')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Supported keys are:
   *   'format' - ImageMagick's image format identifier.
   *   'width' - Image width.
   *   'height' - Image height.
   *   'exif_orientation' - Image EXIF orientation (only supported formats).
   *   'source_local_path' - The local file path from where the file was
   *     parsed.
   *   'frames_count' - Number of frames in the image.
   */
  public function getSupportedKeys($options = NULL) {
    return [
      'format',
      'width',
      'height',
      'exif_orientation',
      'source_local_path',
      'frames_count',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function doGetMetadataFromFile() {
    return $this->identify();
  }

  /**
   * Validates a file metadata key.
   *
   * @return bool
   *   TRUE if the key is valid.
   *
   * @throws \Drupal\file_mdm\FileMetadataException
   *   In case the key is invalid.
   */
  protected function validateKey($key, $method) {
    if (!is_string($key)) {
      throw new FileMetadataException("Invalid metadata key specified", $this->getPluginId(), $method);
    }
    if (!in_array($key, $this->getSupportedKeys(), TRUE)) {
      throw new FileMetadataException("Invalid metadata key '{$key}' specified", $this->getPluginId(), $method);
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function doGetMetadata($key = NULL) {
    if ($key === NULL) {
      return $this->metadata;
    }
    else {
      $this->validateKey($key, __FUNCTION__);
      switch ($key) {
        case 'source_local_path':
          return isset($this->metadata['source_local_path']) ? $this->metadata['source_local_path'] : NULL;

        case 'frames_count':
          return isset($this->metadata['frames']) ? count($this->metadata['frames']) : 0;

        default:
          return isset($this->metadata['frames'][0][$key]) ? $this->metadata['frames'][0][$key] : NULL;

      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doSetMetadata($key, $value) {
    $this->validateKey($key, __FUNCTION__);
    switch ($key) {
      case 'source_local_path':
        $this->metadata['source_local_path'] = $value;
        return TRUE;

      case 'frames_count':
        return FALSE;

      default:
        $this->metadata['frames'][0][$key] = $value;
        return TRUE;

    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doRemoveMetadata($key) {
    $this->validateKey($key, __FUNCTION__);
    switch ($key) {
      case 'source_local_path':
        if (isset($this->metadata['source_local_path'])) {
          unset($this->metadata['source_local_path']);
          return TRUE;
        }
        return FALSE;

      default:
        return FALSE;

    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getMetadataToCache() {
    $metadata = $this->metadata;
    // Avoid caching the source_local_path.
    unset($metadata['source_local_path']);
    return $metadata;
  }

  /**
   * Calls the identify executable on the specified file.
   *
   * @return array
   *   The array with identify metadata, if the file was parsed correctly.
   *   NULL otherwise.
   */
  protected function identify() {
    $arguments = new ImagemagickExecArguments($this->execManager);

    // Add source file.
    $arguments->setSource($this->getLocalTempPath());

    // Prepare the -format argument according to the graphics package in use.
    switch ($this->execManager->getPackage()) {
      case 'imagemagick':
        $arguments->addArgument('-format ' . $this->execManager->escapeShellArg("format:%[magick]|width:%[width]|height:%[height]|exif_orientation:%[EXIF:Orientation]\\n"));
        break;

      case 'graphicsmagick':
        $arguments->addArgument('-format ' . $this->execManager->escapeShellArg("format:%m|width:%w|height:%h|exif_orientation:%[EXIF:Orientation]\\n"));
        break;

    }

    // Allow modules to alter source file and the command line parameters.
    $command = 'identify';
    $this->moduleHandler->alter('imagemagick_pre_parse_file', $arguments);
    $this->moduleHandler->alter('imagemagick_arguments', $arguments, $command);

    // Execute the 'identify' command.
    $output = NULL;
    $ret = $this->execManager->execute($command, $arguments, $output);

    // Process results.
    $data = [];
    if ($ret) {
      // Builds the frames info.
      $frames = [];
      $frames_tmp = explode("\n", $output);
      // Remove empty items at the end of the array.
      while (empty($frames_tmp[count($frames_tmp) - 1])) {
        array_pop($frames_tmp);
      }
      foreach ($frames_tmp as $i => $frame) {
        $info = explode('|', $frame);
        foreach ($info as $item) {
          list($key, $value) = explode(':', $item);
          $frames[$i][trim($key)] = trim($value);
        }
      }
      $data['frames'] = $frames;
      // Adds the local file path that was resolved via
      // hook_imagemagick_pre_parse_file implementations.
      $data['source_local_path'] = $arguments->getSourceLocalPath();
    }

    return ($ret === TRUE) ? $data : NULL;
  }

}
