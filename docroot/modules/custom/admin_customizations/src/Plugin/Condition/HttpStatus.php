<?php

namespace Drupal\admin_customizations\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Provides a "HTTP status" condition.
 *
 * @Condition(
 *   id = "http_status",
 *   label = @Translation("HTTP status"),
 * )
 */
class HttpStatus extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['code'] = [
      '#title' => $this->t('HTTP status'),
      '#type' => 'checkboxes',
      '#options' => [
        403 => $this->t('Access denied (403)'),
        404 => $this->t('Page not found (404)'),
      ],
      '#default_value' => isset($this->configuration['code']) ? $this->configuration['code'] : '',
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['code'] = array_filter($form_state->getValue('code'));

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (empty($this->configuration['code']) && !$this->isNegated()) {
      return TRUE;
    }

    $exception = \Drupal::request()->attributes->get('exception');

    if ($exception instanceof HttpException) {
      return in_array($exception->getStatusCode(), $this->configuration['code']);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('HTTP status code is @not @code', [
      '@code' => implode(', ', $this->configuration['code']),
      '@not' => $this->isNegated() ? 'NOT' : '',
    ]);
  }

}
