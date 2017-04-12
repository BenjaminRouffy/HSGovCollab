<?php

namespace Drupal\group_dashboard\Plugin\views\area;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\page_manager\Entity\Page;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Views area text handler.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("link_to_page")
 */
class LinkToPage extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['title'] = ['default' => ''];
    $options['page'] = ['default' => ''];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['title'] = [
      '#title' => $this->t('Title'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $this->options['title'],
    ];

    $form['page'] = [
      '#title' => $this->t('Page'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'page',
      '#default_value' => Page::load($this->options['page']),
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    if (!$empty || !empty($this->options['empty'])) {
      $page = Page::load($this->options['page']);

      return [
        '#markup' => Link::fromTextAndUrl(
          $this->options['title'],
          Url::fromUserInput($page->get('path'))
        )->toString(),
      ];
    }

    return [];
  }

}
