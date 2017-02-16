<?php

namespace Drupal\sdk\Entity\Form\Sdk;

// Core components.
use Drupal\Core\Render\Element;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
// SDK API components.
use Drupal\sdk\Entity\Sdk;
use Drupal\sdk\Api\Deriver\BaseDeriver;

/**
 * Class DefaultForm.
 *
 * @method Sdk getEntity()
 *
 * @property Sdk $entity
 */
class DefaultForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form_id = strtolower(strtr(static::class, '\\', '_'));
    $types = sdk_types();
    $options = [];

    $this->entity->type = $form_state->getValue('type', $this->entity->type);

    foreach ($types as $option => $info) {
      $options[$option] = $info['label'];
    }

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => $options,
      '#required' => TRUE,
      '#disabled' => !$this->entity->isNew(),
      '#empty_option' => $this->t('- None -'),
      '#default_value' => $this->entity->type,
      '#ajax' => [
        'callback' => '::reloadForm',
        'wrapper' => $form_id,
      ],
    ];

    if (!empty($this->entity->type)) {
      static::populateControllers($this->entity, $form_state, $types);

      $form['label'] = [
        '#type' => 'hidden',
        '#value' => $types[$this->entity->type]['label'],
      ];

      $form['callbackUri'] = [
        '#type' => 'hidden',
        '#value' => $this->entity->getCallbackUrl(FALSE),
      ];

      $form['settings'] = $this->invoke(__FUNCTION__, $form_state);

      static::addWrapper($form['settings'], $form_id . '_' . $this->entity->type);
      static::processSettings($form['settings'], $this->entity->settings);
    }

    static::addWrapper($form, $form_id);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function reloadForm(array $form) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!empty($form['settings'])) {
      // Checks that user creates a new configuration but this kind of types
      // already exists. In this case controller validation will be rejected.
      if ($this->entity->isNew() && NULL !== $this->entityTypeManager->getStorage($this->entity->getEntityTypeId())->load($this->entity->id())) {
        $form_state->setError($form['type'], $this->t('@sdk SDK is already configured! @link', [
          '@sdk' => $this->entity->label(),
          '@link' => $this->entity->link($this->t('Check out it here.'), 'edit-form'),
        ]));
      }
      else {
        $this->invoke(__FUNCTION__, $form_state, $form['settings']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->invoke(__FUNCTION__, $form_state, $form['settings']);

    /* @var BaseDeriver $deriver */
    $deriver = $form_state->getTemporaryValue(['controller', 'deriver']);
    $redirect = $this->entity->toUrl('collection');

    if ($deriver->isLoginCallbackOverridden()) {
      $form_state->setResponse($deriver->requestToken($redirect));
    }
    else {
      $form_state->setRedirectUrl($redirect);
    }
  }

  /**
   * Invoke one of methods from a form controller.
   *
   * @param string $method
   *   Name of method.
   * @param FormStateInterface $form_state
   *   A state of form.
   * @param array[] $form
   *   Form element definitions.
   *
   * @return array|null
   *   Form element definitions or NULL in case of validation or submission.
   */
  protected function invoke($method, FormStateInterface $form_state, array &$form = []) {
    $controller_form = $form_state->getTemporaryValue(['controller', 'form']);

    if (NULL === $controller_form) {
      static::populateControllers($this->entity, $form_state);
    }

    $controller_form = $form_state->getTemporaryValue(['controller', 'form']);

    // @codingStandardsIgnoreStart
    return empty($controller_form) ? [] : call_user_func_array([$controller_form, $method], [&$form, $form_state]);
    // @codingStandardsIgnoreEnd
  }

  /**
   * Populate SDK controllers into a state of form.
   *
   * @param Sdk $entity
   *   An instance of entity object.
   * @param FormStateInterface $form_state
   *   A state of form.
   * @param array|null $types
   *   List of SDK type definitions or NULL.
   */
  protected static function populateControllers(Sdk $entity, FormStateInterface $form_state, array $types = NULL) {
    if (NULL === $types) {
      $types = sdk_types();
    }

    foreach ($types[$entity->type]['classes'] as $controller => $class) {
      $form_state->setTemporaryValue(['controller', $controller], new $class($entity));
    }
  }

  /**
   * Recursively set form settings.
   *
   * @param array[] $form
   *   Form element definitions.
   * @param mixed $settings
   *   Settings list.
   */
  protected static function processSettings(array &$form, &$settings) {
    foreach (Element::children($form) as $child) {
      if (isset($settings[$child])) {
        $form[$child]['#default_value'] = $settings[$child];
      }
      elseif (isset($form[$child]['#default_value'])) {
        $settings[$child] = $form[$child]['#default_value'];
      }

      if (is_array($form[$child])) {
        // @codingStandardsIgnoreStart
        call_user_func_array(__METHOD__, [&$form[$child], &$settings[$child]]);
        // @codingStandardsIgnoreEnd
      }
    }
  }

  /**
   * Add HTML wrapper for an element.
   *
   * @param array $element
   *   Element definition.
   * @param string $id
   *   HTML identifier of an element.
   */
  protected static function addWrapper(array &$element, $id) {
    $element['#tree'] = TRUE;
    $element['#prefix'] = '<div id="' . $id . '">';
    $element['#suffix'] = '</div>';
  }

}
