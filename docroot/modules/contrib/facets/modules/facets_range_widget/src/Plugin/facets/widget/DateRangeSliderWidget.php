<?php

namespace Drupal\facets_range_widget\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\query_type\SearchApiDate;

/**
 * The date range slider widget.
 *
 * @FacetsWidget(
 *   id = "date_range_slider",
 *   label = @Translation("Date range slider"),
 *   description = @Translation("A widget that shows a date range slider."),
 * )
 */
class DateRangeSliderWidget extends RangeSliderWidget {

  /**
   * Human readable array of granularity options.
   *
   * @return array
   *   An array of granularity options.
   */
  private function granularityOptions() {
    return array(
      SearchApiDate::FACETAPI_DATE_YEAR => $this->t('Year'),
      SearchApiDate::FACETAPI_DATE_MONTH => $this->t('Month'),
      SearchApiDate::FACETAPI_DATE_DAY => $this->t('Day'),
/*
      SearchApiDate::FACETAPI_DATE_HOUR => $this->t('Hour'),
      SearchApiDate::FACETAPI_DATE_MINUTE => $this->t('Minute'),
      SearchApiDate::FACETAPI_DATE_SECOND => $this->t('Second'),
*/
    );
  }

  /**
   * Converts granularity to range step.
   *
   * @return int
   *   Step calculated based on granularity.
   */
  protected function getStep() {
    switch ($this->getConfiguration()['granularity']) {
      case SearchApiDate::FACETAPI_DATE_YEAR:
        return 365.25*86400;

      case SearchApiDate::FACETAPI_DATE_MONTH:
      default:
        return 30.4375*86400;

      case SearchApiDate::FACETAPI_DATE_DAY:
        return 86400;

      case SearchApiDate::FACETAPI_DATE_HOUR:
        return 3600;

      case SearchApiDate::FACETAPI_DATE_MINUTE:
        return 60;

      case SearchApiDate::FACETAPI_DATE_SECOND:
        return 1;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'display_relative' => 0,
      'granularity' => SearchApiDate::FACETAPI_DATE_MONTH,
      'date_display' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $configuration['step'] = $this->getStep();
    parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $results = $facet->getResults();
    ksort($results);

    $show_numbers = $facet->getWidgetInstance()->getConfiguration()['show_numbers'];
    $labels = [];

    foreach ($results as $result) {
      $labels[] = $result->getDisplayValue() . ($show_numbers ? ' (' . (int) $result->getCount() . ')' : '');
    }
    $min = (float) reset($results)->getRawValue();
    $max = (float) end($results)->getRawValue();

    $build['slider'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['facet-slider'],
        'id' => $facet->id(),
      ],
    ];

    $active = $facet->getActiveItems();

    $build['#attached']['library'][] = 'facets_range_widget/slider';
    $build['#attached']['drupalSettings']['facets']['sliders'][$facet->id()] = [
      'min' => $min,
      'max' => $max,
      'range' => TRUE,
      'values' => [isset($active[0][0]) ? (float) $active[0][0] : $min, isset($active[0][1]) ? (float) $active[0][1] : $max],
      'url' => reset($results)->getUrl()->toString(),
      'prefix' => $this->getConfiguration()['prefix'],
      'suffix' => $this->getConfiguration()['suffix'],
      'step' => (float) $this->getConfiguration()['step'],
      'labels' => $labels,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $configuration = $this->getConfiguration();

    $form += parent::buildConfigurationForm($form, $form_state, $facet);
    $form['prefix']['#access'] = FALSE;
    $form['suffix']['#access'] = FALSE;
    $form['step']['#access'] = FALSE;

    $form['display_relative'] = [
      '#type' => 'radios',
      '#title' => $this->t('Date display'),
      '#default_value' => $configuration['display_relative'],
      '#options' => [
        0 => $this->t('Actual date with granularity'),
        1 => $this->t('Relative date'),
      ],
    ];

    $form['granularity'] = [
      '#type' => 'radios',
      '#title' => $this->t('Granularity'),
      '#default_value' => $configuration['granularity'],
      '#options' => $this->granularityOptions(),
    ];
    $form['date_display'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format'),
      '#default_value' => $configuration['date_display'],
      '#description' => $this->t('Override default date format used for the displayed filter format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
      '#states' => [
        'visible' => [':input[name="widget_config[display_relative]"]' => ['value' => 0]],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function isPropertyRequired($name, $type) {
    if ($name === 'date_range_slider' && $type === 'processors') {
      return TRUE;
    }
    if ($name === 'show_only_one_result' && $type === 'settings') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType(array $query_types) {
    return $query_types['date_range'];
  }

}
