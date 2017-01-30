<?php

namespace Drupal\datetime_select\Plugin\views\filter;

use DateTime;
use Drupal\Core\Datetime\DateHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\NumericFilter as NumericFilterDefault;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\ViewExecutable;


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

  /* @var \Drupal\views\Plugin\views\query\Sql */
  protected $query;

  public function hasExtraOptions() {
    return TRUE;
  }

  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['type'] = array('default' => 'textfield');
    $options['granularity'] = array('default' => 'month');
    $options['year_range'] = array('default' => '-3:+0');

    return $options;
  }

  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {

    $form['type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Selection type'),
      '#options' => array(
        'select' => $this->t('Select'),
        'textfield' => $this->t('Textfield')
      ),
      '#default_value' => $this->options['type'],
    );


    $form['granularity'] = array(
      '#title' => t('Filter Granularity'),
      '#type' => 'radios',
      '#default_value' => $this->options['granularity'],
      '#options' => $this->granularityOptions(),
    );
    $form['year_range'] = array(
      '#title' => t('Year Range'),
      '#type' => 'textfield',
      '#default_value' => $this->options['year_range'],
    );


  }

  /**
   * Add a type selector to the value form
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $form['value']['#tree'] = TRUE;

    // We have to make some choices when creating this as an exposed
    // filter form. For example, if the operator is locked and thus
    // not rendered, we can't render dependencies; instead we only
    // render the form items we need.
    $which = 'all';
    if (!empty($form['operator'])) {
      $source = ':input[name="options[operator]"]';
    }

    if ($exposed = $form_state->get('exposed')) {
      $identifier = $this->options['expose']['identifier'];

      if (empty($this->options['expose']['use_operator']) || empty($this->options['expose']['operator_id'])) {
        // exposed and locked.
        $which = in_array($this->operator, $this->operatorValues(2)) ? 'minmax' : 'value';
      }
      else {
        $source = ':input[name="' . $this->options['expose']['operator_id'] . '"]';
      }
    }

    $user_input = $form_state->getUserInput();
    if ($which == 'value') {
      // When exposed we drop the value-value and just do value if
      // the operator is locked.

      $form['value'] = array(
        '#type' => 'select',
        '#title' => !$exposed ? $this->t('Value') : '',
        '#options' => $this->selectOptions(), //array_combine($year, $year),
        '#empty_option' => $this->granularityOptions()[$this->options['granularity']],
        '#default_value' => $this->value['value'],
      );
      if ($exposed && !isset($user_input[$identifier])) {
        $user_input[$identifier] = $this->value['value'];
        $form_state->setUserInput($user_input);
      }
    }


  }

  /**
   * Make some translations to a form item to make it more suitable to
   * exposing.
   */
  protected function exposedTranslate(&$form, $type) {
    parent::exposedTranslate($form, $type);


    if ($type == 'value' && empty($this->always_required) && empty($this->options['expose']['required']) && $form['#type'] == 'select' && empty($form['#multiple'])) {
      unset($form['#options']['All']);
    }

  }


  function operators() {
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


  protected function opSimple($field) {
    if ($this->value['value']) {
      $key = ':value' . rand(100, 999);
      $granularity = $this->value['granularity'];
      $this->query->addWhereExpression($this->options['group'], "$granularity(from_unixtime($field)) = $key", [$key => $this->value['value']]);
    }
  }

  private function granularityOptions() {
    return array(
      'year' => t('Year', array(), array('context' => 'datetime')),
      'month' => t('Month', array(), array('context' => 'datetime')),
      //'day' => t('Day', array(), array('context' => 'datetime')),
      /*'hour' => t('Hour', array(), array('context' => 'datetime')),
      'minute' => t('Minute', array(), array('context' => 'datetime')),
      'second' => t('Second', array(), array('context' => 'datetime')),*/
    );
  }
}
