<?php

namespace Drupal\group_dashboard\Plugin\views\filter;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter by node_access records.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("node_operations_access")
 */
class NodeAccess extends FilterPluginBase {
  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return $this->operator . ' ' . $this->options['node_operations'];
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['node_operations'] = ['default' => 'view'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['node_operations'] = [
      '#type' => 'select',
      '#title' => $this->t('Operations'),
      '#required' => TRUE,
      '#options' => [
        'view' => $this->t('View'),
        'update' => $this->t('Update'),
        'delete' => $this->t('Delete'),
      ],
      '#default_value' => $this->options['node_operations'],
    ];
  }

  public function canExpose() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $account = \Drupal::currentUser();
    if (!$account->hasPermission('bypass node access')) {
      $op = $this->options['node_operations'];

      $table = $this->ensureMyTable();
      $grants = new Condition('OR');

      foreach (node_access_grants($op, $account) as $realm => $gids) {
        foreach ($gids as $gid) {
          $grants->condition((new Condition('AND'))
            ->condition($table . '.gid', $gid)
            ->condition($table . '.realm', $realm)
          );
        }
      }

      $this->query->addWhere('AND', $grants);
      $this->query->addWhere('AND', $table . ".grant_$op", 1, '>=');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();

    $contexts[] = 'user.node_grants:' . $this->options['node_operations'];

    return $contexts;
  }

}
