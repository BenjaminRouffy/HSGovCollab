<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use DateTime;
use Drupal\Core\Datetime\DateHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\filter\NumericFilter as NumericFilterDefault;

/**
 * Date/time views filter.
 *
 * Even thought dates are stored as strings, the numeric filter is extended
 * because it provides more sensible operators.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("datetime")
 */
class DateSelect extends NumericFilterDefault implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\views\Plugin\views\query\Sql
   */
  public $query;

  /**
   * Mark form as extra setting form.
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * Default settings.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['type'] = array('default' => 'textfield');
    $options['granularity'] = array('default' => 'month');
    $options['year_range'] = array('default' => '-3:+0');
    $options['from_unixtime'] = array('default' => TRUE);

    return $options;
  }

  /**
   * Extra settings form.
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {

    $form['type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Selection type'),
      '#options' => array(
        'select' => $this->t('Select'),
        'textfield' => $this->t('Textfield'),
      ),
      '#default_value' => $this->options['type'],
    );

    $form['granularity'] = array(
      '#title' => t('Filter Granularity'),
      '#type' => 'radios',
      '#default_value' => $this->options['granularity'],
      '#options' => $this->granularityOptions(),
    );
    $form['from_unixtime'] = array(
      '#title' => t('From Unixtime'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['from_unixtime'],
    );
    $form['year_range'] = array(
      '#title' => t('Year Range'),
      '#type' => 'textfield',
      '#default_value' => $this->options['year_range'],
    );

  }

  /**
   * Add a type selector to the value form.
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    if ($exposed = $form_state->get('exposed') && $this->options['type'] == 'select') {
      $form['value']['#type'] = 'select';
      $form['value']['#options'] = $this->selectOptions();
      $form['value']['#empty_option'] = $this->granularityOptions()[$this->options['granularity']];
      $form['value']['#size'] = 1;

    }
  }

  /**
   * Make some translations to a form item to make it more suitable to exposing.
   */
  protected function exposedTranslate(&$form, $type) {
    parent::exposedTranslate($form, $type);

    if ($type == 'value' && empty($this->always_required) && empty($this->options['expose']['required']) && $form['#type'] == 'select' && empty($form['#multiple'])) {
      unset($form['#options']['All']);
    }

  }

  /**
   * Helper operation filter.
   */
  public function operators() {
    $operators = array(
      '=' => array(
        'title' => $this->t('Is equal to'),
        'method' => 'opSimple',
        'short' => $this->t('='),
        'values' => 1,
      ),
    );

    return $operators;
  }

  /**
   * Helper select value filter.
   */
  private function selectOptions() {
    $options = [];
    switch ($this->options['granularity']) {
      case 'year':
        list($start_year, $end_year) = explode(':', $this->options['year_range']);
        /* @var $start DateTime */
        $start = new DateTime();
        $start->modify("$start_year year");
        /* @var $end DateTime */
        $end = new DateTime();
        $end->modify("$end_year year");
        $year = range($start->format('Y'), $end->format('Y'));
        $options = array_combine($year, $year);
        asort($options);
        break;

      case 'month':
        $options = DateHelper::monthNamesUntranslated();
        break;
    }
    return $options;
  }

  /**
   * Singe operator query alter.
   */
  protected function opSimple($field) {
    if ($this->value['value']) {
      $key = ':value' . rand(100, 999);
      $granularity = $this->options['granularity'];
      $from_unixtime = $this->options['from_unixtime'] ? 'from_unixtime' : '';

      $this->query->addWhereExpression($this->options['group'], "$granularity($from_unixtime($field)) = $key", [$key => $this->value['value']]);
    }
  }

  /**
   * Helper granularity option.
   */
  private function granularityOptions() {
    return array(
      'year' => t('Year', array(), array('context' => 'datetime')),
      'month' => t('Month', array(), array('context' => 'datetime')),
    );
  }

}
