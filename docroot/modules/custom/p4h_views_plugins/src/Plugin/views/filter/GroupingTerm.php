<?php

namespace Drupal\p4h_views_plugins\Plugin\views\filter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group_following\GroupFollowingManagerInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\taxonomy\VocabularyStorageInterface;
use Drupal\views\Annotation\ViewsFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by term id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("combine_taxonomy_term")
 */
class GroupingTerm extends GroupIndexGid  {

  /**
   * The vocabulary storage.
   *
   * @var VocabularyStorageInterface
   */
  protected $vocabularyStorage;

  /**
   * The term storage.
   *
   * @var TermStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VocabularyStorageInterface $vocabulary_storage, TermStorageInterface $term_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->vocabularyStorage = $vocabulary_storage;
    $this->termStorage = $term_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('taxonomy_vocabulary'),
      $container->get('entity.manager')->getStorage('taxonomy_term')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['vid'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $vocabularies = $this->vocabularyStorage->loadMultiple();
    $options = [];

    foreach ($vocabularies as $voc) {
      $options[$voc->id()] = $voc->label();
    }

    $form['vid'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Taxonomies'),
      '#options' => $options,
      '#default_value' => $this->options['taxonomy'],
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $options = [];

    foreach (array_filter($this->options['vid']) as $id) {
      $tree = $this->termStorage->loadTree($id, 0, NULL, TRUE);

      if (!empty($tree)) {
        /* @var Term $term */
        foreach ($tree as $term) {
          $options[$term->id()] = \Drupal::entityManager()
            ->getTranslationFromContext($term)
            ->label();
        }
      }
    }

    $options = array_reverse($options);

    $form_state->set('filter_options', $options);

    parent::valueForm($form, $form_state);
  }

}
