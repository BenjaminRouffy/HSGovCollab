<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\Combine as DefaultCombine;

/**
 * Filter handler which allows to search on multiple fields.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsFilter("combine")
 */
class CustomCombine extends DefaultCombine {

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

    $options['alphabet'] = array('default' => FALSE);

    return $options;
  }

  /**
   * Extra settings form.
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {

    $form['alphabet'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Alphabet'),
      '#default_value' => $this->options['alphabet'],
    );
  }

  /**
   * Provide a simple textfield for equality.
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    if ($exposed = $form_state->get('exposed') && !empty($this->options['alphabet'])) {
      $alphabet = str_split(implode(range('A', 'Z')), 3);
      $form['value']['#type'] = 'select';
      $form['value']['#options'] = array_merge(['all' => $this->t('ALL')], array_combine($alphabet, $alphabet));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function opStartsWith($expression) {
    /* @var $query \Drupal\views\Plugin\views\query\Sql */
    $query = &$this->query;
    if (empty($this->options['alphabet'])) {
      return parent::opStartsWith($expression);
    }
    // @TODO Remove this weird hard code.
    if (empty($query->where[1]['conditions'][0]['value'][':views_combine']) && "all" != $this->value) {
      $placeholder = $this->placeholder();
      $expression = "SUBSTR({$expression},1,1) IN ({$placeholder}[])";
      $query->addWhereExpression($this->options['group'], $expression, [
        "{$placeholder}[]" => str_split($this->value, 1)
      ]);

    }
  }

}
