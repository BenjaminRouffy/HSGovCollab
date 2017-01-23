<?php

namespace Drupal\events\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeWidgetBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'daterange_eventdaterange' widget.
 *
 * @FieldWidget(
 *   id = "daterange_eventdaterange",
 *   label = @Translation("Event date range"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class EventDateRangeWidget extends DateRangeWidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Values for the 'date_type' setting.
   */
  const DATERANGE_TYPE_DATEONLY = 'date_only';
  const DATERANGE_TYPE_TIMEONLY = 'time_only';

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateStorage;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'date_format' => 'html_date',
      'date_type' => 'date_only',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $formats_data = DateFormat::loadMultiple();
    $formats = [];

    foreach ($formats_data as $key => $value) {
      $formats[$key] = $value->label();
    }

    $date_types = [
      self::DATERANGE_TYPE_DATEONLY => $this->t('Date only'),
      self::DATERANGE_TYPE_TIMEONLY => $this->t('Time only'),
    ];

    $element['date_type'] = [
      '#type' => 'select',
      '#options' => $date_types,
      '#multiple' => FALSE,
      '#title' => $this->t('Date type'),
      '#default_value' => $this->getSetting('date_type'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $date_types = [
      self::DATERANGE_TYPE_DATEONLY => $this->t('Date only'),
      self::DATERANGE_TYPE_TIMEONLY => $this->t('Time only'),
    ];
    $summary[] = $this->t('Date type: @type', ['@type' => $date_types[$this->getSetting('date_type')]]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityStorageInterface $date_storage) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->dateStorage = $date_storage;
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
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')->getStorage('date_format')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Identify the type of date and time elements to use.
    switch ($this->getSetting('date_type')) {
      case self::DATERANGE_TYPE_TIMEONLY:
        $date_type = 'none';
        $time_type = 'time';
        break;

      case self::DATERANGE_TYPE_DATEONLY:
        $date_type = 'date';
        $time_type = 'none';
        break;

      default:
        break;
    }

    $date_format = $this->dateStorage->load('html_date')->getPattern();
    $time_format = $this->dateStorage->load('html_time')->getPattern();

    $element['value'] += [
      '#date_date_format' => $date_format,
      '#date_date_element' => $date_type,
      '#date_date_callbacks' => [],
      '#date_time_format' => $time_format,
      '#date_time_element' => $time_type,
      '#date_time_callbacks' => [],
    ];

    $element['end_value'] += [
      '#date_date_format' => $date_format,
      '#date_date_element' => $date_type,
      '#date_date_callbacks' => [],
      '#date_time_format' => $time_format,
      '#date_time_element' => $time_type,
      '#date_time_callbacks' => [],
    ];

    return $element;
  }

}
