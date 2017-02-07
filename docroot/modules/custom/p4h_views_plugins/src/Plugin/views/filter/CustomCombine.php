<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\filter\Combine as DefaultCombine;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter handler which allows to search on multiple fields.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsFilter("combine")
 */
class CustomCombine extends DefaultCombine implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new Date handler.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack used to determine the current time.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

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
   * Provide a simple textfield for equality
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    if ($exposed = $form_state->get('exposed') && !empty($this->options['alphabet'])) {
      $alphabet = str_split(implode(range('A', 'Z')), 3);
      $form['value']['#type'] = 'select';
      $form['value']['#options'] = array_combine($alphabet, $alphabet);
    }
  }

  protected function opStartsWith($expression) {
    /* @var $query \Drupal\views\Plugin\views\query\Sql */
    $query = &$this->query;
    if(empty($this->options['alphabet'])) {
      return parent::opStartsWith($expression);
    }
    // @TODO A REGEXP might be more efficient, but you'd have to benchmark it to be sure, e.g.
    /* @var $or \Drupal\Core\Database\Query\Condition */
    $this->query->setWhereGroup('OR', 'alphabet');
    foreach (str_split($this->value, 1) as $item) {
      $placeholder = $this->placeholder();
      $query->addWhereExpression('alphabet', "$expression LIKE $placeholder", array($placeholder => db_like($item) . '%'));
    }
  }

}
