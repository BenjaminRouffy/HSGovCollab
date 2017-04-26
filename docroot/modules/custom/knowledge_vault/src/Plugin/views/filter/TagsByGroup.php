<?php

namespace Drupal\knowledge_vault\Plugin\views\filter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group_following\GroupFollowingManagerInterface;
use Drupal\p4h_views_plugins\Plugin\views\filter\GroupIndexGid;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\taxonomy\VocabularyStorageInterface;
use Drupal\views\Annotation\ViewsFilter;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by term id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("tags_by_group")
 */
class TagsByGroup extends GroupIndexGid  {
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

    $options['tags'] = ['default' => ''];
    $options['count'] = ['default' => 20];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $form['tags'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Tags style'),
      '#default_value' => $this->options['tags'],
    ];

    $form['count'] = [
      '#type' => 'number',
      '#title' => $this->t('Tags count'),
      '#default_value' => $this->options['count'],
      '#min' => 1,
      '#max' => 50,
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $options = [];

    // @todo Change it.
    $tags = views_get_view_result('tagadelic_terms', 'tags_by_group', $this->view->args[0]);

    if (count($tags) > $this->options['count']) {
      $rand = array_rand($tags, $this->options['count']);

      foreach ($rand as $index => $tag) {
        $rand[$index] = $tags[$tag];
      }

      $tags = $rand;
    }

    shuffle($tags);

    foreach ($tags as $tag) {
      $options[$tag->_entity->id()] = \Drupal::entityManager()
        ->getTranslationFromContext($tag->_entity)
        ->label();
    }

    if (!empty($this->options['tags'])) {
      $form['#tags_styles'][$this->field] = $tags;
    }

    $form_state->set('filter_options', $options);

    parent::valueForm($form, $form_state);
  }

}
