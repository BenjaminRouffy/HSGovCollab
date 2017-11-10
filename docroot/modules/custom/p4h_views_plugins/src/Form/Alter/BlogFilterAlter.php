<?php

namespace Drupal\p4h_views_plugins\Form\Alter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\form_alter_service\Interfaces\FormAlterServiceAlterInterface;
use Drupal\form_alter_service\Interfaces\FormAlterServiceBaseInterface;
use Drupal\tagadelic\TagadelicCloudBase;
use Drupal\tagadelic\TagadelicTag;
use Drupal\views\ResultRow;

/**
 * Class DefaultUserEditAlter.
 */
class BlogFilterAlter implements FormAlterServiceBaseInterface, FormAlterServiceAlterInterface {

  /**
   * Checks that form is matched to specific conditions.
   *
   * @return bool
   */
  public function hasMatch(&$form, FormStateInterface $form_state, $form_id) {
    return 'views-exposed-form-blog-lists-blog-posts' == $form['#id'];
  }

  /**
   * Form alter custom implementation.
   *
   * @param $form
   * @param FormStateInterface $form_state
   */
  public function formAlter(&$form, FormStateInterface $form_state) {
    $form['#attributes']['data-links-filter'] = $form['#id'];

    foreach (Element::getVisibleChildren($form) as $name) {
      if (isset($form[$name]['#type']) && 'select' === $form[$name]['#type'] && 'content_tags_cloud' == $name) {
        $tags = [];
        $options = [];

        if (!empty($form['#tags_styles'][$name])) {
          $tags = $this->generateTagCloude($form['#tags_styles'][$name]);
        }
        // Extract the options from the Views Exposed Filter <select>-list
        $links = $form[$name]['#options'];

        if (!empty($form['#tags_styles'][$name])) {
          unset($links['All']);
        }

        foreach ($links as $tid => $term_name) {
          $classes = ['filter-tab'];

          if (!empty($form['#tags_styles'][$name]) && isset($tags[$tid])) {
            $classes[] = 'level' . round($tags[$tid] / 2);
          }

          $element = [
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#attributes' => [
              'class' => $classes,
            ],
            '#value' => '<a href="" id="' . $tid . '">' . $term_name . '</a>',
          ];

          if ($tid == 'All') {
            $element['#value'] = '<a href="" class="active" id="' . $tid . '">' . t('All') . '</a>';
          }

          $options[] = $element;
        }

        $form[$name . '_wrap'] = [
          '#prefix' => '<div class="wrapper-' . str_replace('_', '-', $name) . '"',
          '#suffix' => '</div>',
          '#weight' => -100,
        ];

        $form[$name . '_wrap'][$name] = $form[$name];

        unset($form[$name]);

        if (!empty($form['#info']["filter-$name"]['label'])) {
          $form[$name . '_wrap'][$name . '_label'] = [
            '#type' => 'markup',
            '#markup' => $form['#info']["filter-$name"]['label'],
            '#prefix' => "<h3 class='filter-label " . "$name'>",
            '#suffix' => '</h3>',
          ];
        }

        $form[$name . '_wrap'][$name . '_links'] = [
          '#theme' => 'item_list',
          '#items' => $options,
          '#attributes' => ['class' => [str_replace('_', '-', $name)]],
        ];

        $form['title']['#attributes']['placeholder'] = t('Search in blogs');

        $form['title']['#prefix'] = '<div class="search-form-wrapper">';
        $form['actions']['#suffix'] = '</div>';
      }
    }

    $form['#attached']['drupalSettings']['blog_view'] = 'blog_posts';
    $form['#attached']['library'][] = 'ample/tagcloud-filter';
  }

  /**
   * Helper function for generate tag weight.
   *
   * @param array $options
   *   List of tags.
   *
   * @return array
   *  Tags weight.
   *
   * @see TagadelicCloudBase::recalculate().
   */
  private function generateTagCloude(array $options) {
    $min = 1e9;
    $max = -1e9;
    $steps = 6;
    $tags = [];

    /* @var ResultRow $tag */
    foreach ($options as $tag) {
      $tags[] = new TagadelicTag($tag->_entity->id(), $tag->_entity->label(), $tag->tid);
    }

    /* @var TagadelicTag $tag */
    foreach ($tags as $id => $tag) {
      $min = min($min, $tag->distributed());
      $max = max($max, $tag->distributed());
    }

    // Note: we need to ensure the range is slightly too large to make sure even
    // the largest element is rounded down.
    $range = max(.01, $max - $min) * 1.0001;

    foreach ($tags as $id => $tag) {
      $tags[$tag->getId()] = 1 + floor($steps * ($tag->distributed() - $min) / $range);
      unset($tags[$id]);
    }

    return $tags;
  }

}
