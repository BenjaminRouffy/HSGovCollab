<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Datetime\DateHelper;
use DateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ingroup views_filter_handlers
 * @ViewsFilter("computed_date")
 */
class ComputedDate extends ManyToOne {

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
   * Mark form as extra setting form.
   */
  public function hasExtraOptions() {
    return TRUE;
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
  function operators() {
    $operators = array(
      'in' => array(
        'title' => $this->t('Is one of'),
        'short' => $this->t('in'),
        'short_single' => $this->t('='),
        'method' => 'opSimple',
        'values' => 1,
      ),
    );

    return $operators;
  }

  protected function opSimple() {
    if (empty($this->value)) {
      return;
    }
    $this->ensureMyTable();

    $field = "$this->tableAlias.$this->realField";

    $granularity = $this->options['granularity'];
    $placeholder = $this->placeholder();

    $this->query->addWhereExpression($this->options['group'], "$granularity($field) {$this->operator} ({$placeholder}[])", [
      "{$placeholder}[]" => array_values($this->value)
    ]);
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
        arsort($options);
        break;

      case 'month':
        $options = DateHelper::monthNamesUntranslated();
        break;
    }
    return $options;
  }

  public function getValueOptions() {
    $this->valueOptions = $this->selectOptions();
    return $this->valueOptions;
  }

}
