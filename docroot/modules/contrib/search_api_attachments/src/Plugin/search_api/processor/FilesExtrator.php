<?php

namespace Drupal\search_api_attachments\Plugin\search_api\processor;

use Drupal\Component\Utility\Bytes;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Utility\FieldsHelperInterface;
use Drupal\search_api_attachments\Plugin\search_api\processor\Property\FilesExtractorProperty;
use Drupal\search_api_attachments\TextExtractorPluginInterface;
use Drupal\search_api_attachments\TextExtractorPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides file fields processor.
 *
 * @SearchApiProcessor(
 *   id = "file_attachments",
 *   label = @Translation("File attachments"),
 *   description = @Translation("Adds the file attachments content to the indexed data."),
 *   stages = {
 *     "add_properties" = 0,
 *   }
 * )
 */
class FilesExtrator extends ProcessorPluginBase implements PluginFormInterface {

  /**
   * Name of the config being edited.
   */
  const CONFIGNAME = 'search_api_attachments.admin_config';

  /**
   * Property name of the fields provided by this module.
   */
  const SAA_PROPERTY = 'saa_file_property';

  /**
   * The plugin manager for our text extractor.
   *
   * @var \Drupal\search_api_attachments\TextExtractorPluginManager
   */
  protected $textExtractorPluginManager;

  /**
   * The mime type guesser service.
   *
   * @var \Drupal\Core\File\MimeType\MimeTypeGuesser
   */
  protected $mimeTypeGuesser;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Key value service.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Search API field helper.
   *
   * @var \Drupal\search_api\Utility\FieldsHelperInterface
   */
  protected $fieldHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, TextExtractorPluginManager $text_extractor_plugin_manager, MimeTypeGuesserInterface $mime_type_guesser, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, KeyValueFactoryInterface $key_value, ModuleHandlerInterface $module_handler, FieldsHelperInterface $field_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->textExtractorPluginManager = $text_extractor_plugin_manager;
    $this->mimeTypeGuesser = $mime_type_guesser;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->keyValue = $key_value;
    $this->moduleHandler = $module_handler;
    $this->fieldHelper = $field_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('plugin.manager.search_api_attachments.text_extractor'), $container->get('file.mime_type.guesser'), $container->get('config.factory'), $container->get('entity_type.manager'), $container->get('keyvalue'), $container->get('module_handler'), $container->get('search_api.fields_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Search API attachments'),
        'description' => $this->t('Search API attachments'),
        'type' => 'string',
        'processor_id' => $this->getPluginId(),
      ];
      $properties[static::SAA_PROPERTY] = new FilesExtractorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $config = $this->configFactory->getEditable(static::CONFIGNAME);
    $extractor_plugin_id = $config->get('extraction_method');
    if ($extractor_plugin_id == '') {
      return;
    }

    $configuration = $config->get($extractor_plugin_id . '_configuration');
    $extractor_plugin = $this->textExtractorPluginManager->createInstance($extractor_plugin_id, $configuration);

    $file_extract_fields = $this->getFieldsHelper()
      ->filterForPropertyPath($item->getFields(), NULL, static::SAA_PROPERTY);
    foreach ($file_extract_fields as $field) {
      $all_fids = [];

      // Loop through the extracted fields to get the file ids.
      foreach ($field->getConfiguration()['fields'] as $field_name) {
        $extracted_field = $item->getField($field_name);
        if ($extracted_field && $extracted_field_values = $extracted_field->getValues()) {
          foreach ($extracted_field_values as $extracted_field_value) {
            $all_fids[] = $extracted_field_value;
          }
        }
      }

      $fids = $this->limitToAllowedNumber($all_fids);

      // Retrieve the files.
      $files = $this->entityTypeManager
        ->getStorage('file')
        ->loadMultiple($fids);

      if (!empty($files)) {
        foreach ($files as $file) {
          if ($this->isFileIndexable($file, $item, $field)) {
            $field->addValue($this->extractOrGetFromCache($file, $extractor_plugin));
          }
        }
      }
    }
  }

  /**
   * Extract file data or get it from cache if available and cache it.
   *
   * @param \Drupal\file\Entity\File $file
   *   A file object.
   * @param \Drupal\search_api_attachments\TextExtractorPluginInterface $extractor_plugin
   *   The plugin used to extract file content.
   *
   * @return string
   *   $extracted_data
   */
  public function extractOrGetFromCache(File $file, TextExtractorPluginInterface $extractor_plugin) {
    $collection = 'search_api_attachments';
    $key = $collection . ':' . $file->id();
    if ($cache = $this->keyValue->get($collection)->get($key)) {
      $extracted_data = $cache;
    }
    else {
      $extracted_data = $extractor_plugin->extract($file);
      $this->keyValue->get($collection)->set($key, $extracted_data);
    }
    return $extracted_data;
  }

  /**
   * Limit the number of items to index per field to the configured limit.
   *
   * @param array $all_fids
   *   Array of fids.
   *
   * @return array
   *   An array of $limit number of items.
   */
  public function limitToAllowedNumber(array $all_fids) {
    $limit = 0;
    if (isset($this->configuration['number_indexed'])) {
      $limit = $this->configuration['number_indexed'];
    }
    // If limit is 0 return all items.
    if ($limit == 0) {
      return $all_fids;
    }
    if (count($all_fids) > $limit) {
      return array_slice($all_fids, 0, $limit);
    }
    else {
      return $all_fids;
    }
  }

  /**
   * Check if the file is allowed to be indexed.
   *
   * @param object $file
   *   A file object.
   * @param \Drupal\search_api\Item\ItemInterface $item
   *   The item the file was referenced in.
   * @param string|null $field_name
   *   The name of the field the file was referenced in, if applicable.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function isFileIndexable($file, ItemInterface $item, $field_name = NULL) {
    // File should exist in disc.
    $indexable = file_exists($file->getFileUri());
    if (!$indexable) {
      return FALSE;
    }
    // File should have a mime type that is allowed.
    $indexable = $indexable && !in_array($file->getMimeType(), $this->getExcludedMimes());
    if (!$indexable) {
      return FALSE;
    }
    // File permanent.
    $indexable = $indexable && $file->isPermanent();
    if (!$indexable) {
      return FALSE;
    }
    // File shouldn't exceed configured file size.
    $indexable = $indexable && $this->isFileSizeAllowed($file);
    if (!$indexable) {
      return FALSE;
    }
    // Whether a private file can be indexed or not.
    $indexable = $indexable && $this->isPrivateFileAllowed($file);
    if (!$indexable) {
      return FALSE;
    }
    $result = $this->moduleHandler->invokeAll(
        'search_api_attachments_indexable', [$file, $item, $field_name]
    );
    $indexable = !in_array(FALSE, $result, TRUE);
    return $indexable;
  }

  /**
   * Exclude files that exceed configured max size.
   *
   * @param object $file
   *   File object.
   *
   * @return bool
   *   TRUE if the file size does not exceed configured max size.
   */
  public function isFileSizeAllowed($file) {
    if (isset($this->configuration['max_filesize'])) {
      $configured_size = $this->configuration['max_filesize'];
      if ($configured_size == '0') {
        return TRUE;
      }
      else {
        $file_size_bytes = $file->getSize();
        $configured_size_bytes = Bytes::toInt($configured_size);
        if ($file_size_bytes > $configured_size_bytes) {
          return FALSE;
        }
      }
    }

    return TRUE;
  }

  /**
   * Exclude private files from being indexed.
   *
   * Only happens if the module is configured to do so(default behaviour).
   *
   * @param object $file
   *   File object.
   *
   * @return bool
   *   TRUE if we should prevent current file from being indexed.
   */
  public function isPrivateFileAllowed($file) {
    // Know if private files are allowed to be indexed.
    $private_allowed = FALSE;
    if (isset($this->configuration['excluded_private'])) {
      $private_allowed = !(bool)$this->configuration['excluded_private'];
    }
    // Know if current file is private.
    $uri = $file->getFileUri();
    $file_is_private = FALSE;
    if (substr($uri, 0, 10) == 'private://') {
      $file_is_private = TRUE;
    }

    if (!$file_is_private) {
      return TRUE;
    }
    else {
      return $private_allowed;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    if (isset($this->configuration['excluded_extensions'])) {
      $default_excluded_extensions = $this->configuration['excluded_extensions'];
    }
    else {
      $default_excluded_extensions = $this->defaultExcludedExtensions();
    }
    $form['excluded_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Excluded file extensions'),
      '#default_value' => $default_excluded_extensions,
      '#size' => 80,
      '#maxlength' => 255,
      '#description' => $this->t('File extensions that are excluded from indexing. Separate extensions with a space and do not include the leading dot.<br />Example: "aif art avi bmp gif ico mov oga ogv png psd ra ram rgb flv"<br />Extensions are internally mapped to a MIME type, so it is not necessary to put variations that map to the same type (e.g. tif is sufficient for tif and tiff)'),
    ];
    $form['number_indexed'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of files indexed per file field'),
      '#default_value' => isset($this->configuration['number_indexed']) ? $this->configuration['number_indexed'] : '0',
      '#size' => 5,
      '#min' => 0,
      '#max' => 99999,
      '#description' => $this->t('The number of files to index per file field.<br />The order of indexation is the weight in the widget.<br /> 0 for no restriction.'),
    ];
    $form['max_filesize'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum upload size'),
      '#default_value' => isset($this->configuration['max_filesize']) ? $this->configuration['max_filesize'] : '0',
      '#description' => $this->t('Enter a value like "10 KB", "10 MB" or "10 GB" in order to restrict the max file size of files that should be indexed.<br /> Enter "0" for no limit restriction.'),
      '#size' => 10,
    ];
    $form['excluded_private'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude private files'),
      '#default_value' => isset($this->configuration['excluded_private']) ? $this->configuration['excluded_private'] : TRUE,
      '#description' => $this->t('Check this box if you want to exclude private files from being indexed.'),
    ];
    return $form;
  }

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   *
   * @see \Drupal\Core\Plugin\PluginFormInterface::validateConfigurationForm()
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $max_filesize = trim($form_state->getValue('max_filesize'));
    if ($max_filesize != '0') {
      $size_info = explode(' ', $max_filesize);
      if (count($size_info) != 2) {
        $error = TRUE;
      }
      else {
        $starts_integer = is_int((int) $size_info[0]);
        $unit_expected = in_array($size_info[1], ['KB', 'MB', 'GB']);
        $error = !$starts_integer || !$unit_expected;
      }
      if ($error) {
        $form_state->setErrorByName('max_filesize', $this->t('The max filesize option must contain a valid value. You may either enter "0" (for no restriction) or a string like "10 KB, "10 MB" or "10 GB".'));
      }
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   *
   * @see \Drupal\Core\Plugin\PluginFormInterface::submitConfigurationForm()
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $excluded_extensions = $form_state->getValue('excluded_extensions');
    $excluded_extensions_array = explode(' ', $excluded_extensions);
    $excluded_mimes_array = $this->getExcludedMimes($excluded_extensions_array);
    $excluded_mimes_string = implode(' ', $excluded_mimes_array);
    $this->setConfiguration($form_state->getValues() + ['excluded_mimes' => $excluded_mimes_string]);
  }

  /**
   * Default excluded extensions.
   *
   * @return string
   *   string of file extensions separated by a space.
   */
  public function defaultExcludedExtensions() {
    return 'aif art avi bmp gif ico mov oga ogv png psd ra ram rgb flv';
  }

  /**
   * Get a corresponding array of excluded mime types.
   *
   * Obtained from a space separated string of file extensions.
   *
   * @param string $extensions
   *   If it's not null, the return will correspond to the extensions.
   *   If it is null,the return will correspond to the default excluded
   *   extensions.
   *
   * @return array
   *   Array or mimes.
   */
  public function getExcludedMimes($extensions = NULL) {
    if (!$extensions && isset($this->configuration['excluded_mimes'])) {
      $excluded_mimes_string = $this->configuration['excluded_mimes'];
      $excluded_mimes = explode(' ', $excluded_mimes_string);
    }
    else {
      if (!$extensions) {
        $extensions = explode(' ', $this->defaultExcludedExtensions());
      }
      $excluded_mimes = [];
      foreach ($extensions as $extension) {
        $excluded_mimes[] = $this->mimeTypeGuesser->guess('dummy.' . $extension);
      }
    }
    // Ensure we get an array of unique mime values because many extension can
    // map the the same mime type.
    $excluded_mimes = array_combine($excluded_mimes, $excluded_mimes);
    return array_keys($excluded_mimes);
  }

}
