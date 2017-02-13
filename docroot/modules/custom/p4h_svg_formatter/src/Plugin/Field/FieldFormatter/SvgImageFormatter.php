<?php

namespace Drupal\p4h_svg_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\p4h_svg_formatter\AbstractFetchManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'svg_image_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "svg_image_formatter",
 *   label = @Translation("Svg image formatter"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class SvgImageFormatter extends ImageFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal service implementation.
   *
   * @var \Drupal\p4h_svg_formatter\AbstractFetchManager
   */
  public $fetchManager;

  /**
   * Drupal service implementation.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  public $fileSystem;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AbstractFetchManager $fetch_manager, FileSystemInterface $file_system) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->fetchManager = $fetch_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('p4h_stage_file_proxy.fetch_manager'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    foreach ($files as $delta => $file) {
      $image_uri = $file->getFileUri();

      $path = $this->fileSystem->realpath($image_uri);
      if (!file_exists($path)) {
        $path = $this->fetchManager
          ->download($image_uri);
      }
      $elements[$delta] = [
        [
          '#type' => 'inline_template',
          '#template' => '{{ file_content | raw }}',
          '#context' => [
            'file_content' => file_get_contents($path)
          ]
        ]
      ];
    }

    return $elements;
  }

}
