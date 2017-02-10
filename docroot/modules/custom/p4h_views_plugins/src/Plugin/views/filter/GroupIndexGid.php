<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by term id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_index_gid")
 */
class GroupIndexGid extends ManyToOne {

  public $group_type;
  public $group;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigEntityStorageInterface $group_type, SqlEntityStorageInterface $group) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->group_type = $group_type;
    $this->group = $group;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('group_type'),
      $container->get('entity.manager')->getStorage('group')
    );
  }

  public function hasExtraOptions() { return TRUE; }

  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['gid'] = array('default' => '');

    return $options;
  }

  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $group_type = $this->group_type->loadMultiple();
    $options = array();
    foreach ($group_type as $entity_type) {
      $options[$entity_type->id()] = $entity_type->label();
    }

    $form['gid'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Group type'),
      '#options' => $options,
      '#default_value' => $this->options['vid'],
    );

  }

  function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    $group_type = $this->group_type->load($this->options['gid']);

    $options = array();
    $query = \Drupal::entityQuery('group')
      // @todo Sorting on vocabulary properties -
      //   https://www.drupal.org/node/1821274.
      ->addTag('group_access');
    $query->condition('type', $group_type->id());
    $groups = $this->group->loadMultiple($query->execute());
    foreach ($groups as $group) {
      $options[$group->id()] = \Drupal::entityManager()->getTranslationFromContext($group)->label();
    }

    $default_value = (array) $this->value;

    if ($exposed = $form_state->get('exposed')) {
      $identifier = $this->options['expose']['identifier'];

      if (!empty($this->options['expose']['reduce'])) {
        $options = $this->reduceValueOptions($options);

        if (!empty($this->options['expose']['multiple']) && empty($this->options['expose']['required'])) {
          $default_value = array();
        }
      }

      if (empty($this->options['expose']['multiple'])) {
        if (empty($this->options['expose']['required']) && (empty($default_value) || !empty($this->options['expose']['reduce']))) {
          $default_value = 'All';
        }
        elseif (empty($default_value)) {
          $keys = array_keys($options);
          $default_value = array_shift($keys);
        }
        // Due to #1464174 there is a chance that array('') was saved in the admin ui.
        // Let's choose a safe default value.
        elseif ($default_value == array('')) {
          $default_value = 'All';
        }
        else {
          $copy = $default_value;
          $default_value = array_shift($copy);
        }
      }
    }
    $form['value'] = array(
      '#type' => 'select',
      '#title' => $this->options['limit'] ? $this->t('Select terms from vocabulary @voc', array('@voc' => $group_type->label())) : $this->t('Select terms'),
      '#multiple' => TRUE,
      '#options' => $options,
      '#size' => min(9, count($options)),
      '#default_value' => $default_value,
    );

    $user_input = $form_state->getUserInput();
    if ($exposed && isset($identifier) && !isset($user_input[$identifier])) {
      $user_input[$identifier] = $default_value;
      $form_state->setUserInput($user_input);
    }
  }


}
