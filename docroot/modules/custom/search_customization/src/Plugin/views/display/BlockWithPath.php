<?php

namespace Drupal\search_customization\Plugin\views\display;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\display\Block;

/**
 * View display for search.
 *
 * @ViewsDisplay(
 *   id = "block_with_path",
 *   title = @Translation("Block with path"),
 *   help = @Translation("Display the view as a block and has a path but provided by the panels."),
 *   theme = "views_view",
 *   register_theme = FALSE,
 *   uses_hook_block = TRUE,
 *   contextual_links_locations = {"block"},
 *   admin = @Translation("Block with path")
 * )
 */
class BlockWithPath extends Block {

  /**
   * {@inheritdoc}
   */
  public function hasPath() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    $bits = explode('/', $this->getOption('path'));

    return implode('/', $bits);
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    $categories['page'] = [
      'title' => $this->t('Page settings'),
      'column' => 'second',
      'build' => [
        '#weight' => -10,
      ],
    ];

    $path = strip_tags($this->getOption('path'));

    if (empty($path)) {
      $path = $this->t('No path is set');
    }
    else {
      $path = '/' . $path;
    }

    $options['path'] = [
      'category' => 'page',
      'title' => $this->t('Path'),
      'value' => views_ui_truncate($path, 24),
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    switch ($form_state->get('section')) {
      case 'path':
        $form['#title'] .= $this->t('The menu path or URL of this view');
        $form['path'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Path'),
          '#description' => $this->t('This view will be displayed by visiting this path on your site. You may use "%" in your URL to represent values that will be used for contextual filters: For example, "node/%/feed". If needed you can even specify named route parameters like taxonomy/term/%taxonomy_term'),
          '#default_value' => $this->getOption('path'),
          '#field_prefix' => '<span dir="ltr">' . Url::fromRoute('<none>', [], ['absolute' => TRUE])->toString(),
          '#field_suffix' => '</span>&lrm;',
          '#attributes' => ['dir' => LanguageInterface::DIRECTION_LTR],
          // Account for the leading backslash.
          '#maxlength' => 254,
        ];
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    parent::validateOptionsForm($form, $form_state);

    if ($form_state->get('section') == 'path') {
      $errors = $this->validatePath($form_state->getValue('path'));
      foreach ($errors as $error) {
        $form_state->setError($form['path'], $error);
      }

      // Automatically remove '/' and trailing whitespace from path.
      $form_state->setValue('path', trim($form_state->getValue('path'), '/ '));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    parent::submitOptionsForm($form, $form_state);

    if ($form_state->get('section') == 'path') {
      $this->setOption('path', $form_state->getValue('path'));
    }
  }

  /**
   * Validates the path of the display.
   *
   * @param string $path
   *   The path to validate.
   *
   * @return array
   *   A list of error strings.
   */
  protected function validatePath($path) {
    $errors = [];
    if (strpos($path, '%') === 0) {
      $errors[] = $this->t('"%" may not be used for the first segment of a path.');
    }

    $parsed_url = UrlHelper::parse($path);
    if (empty($parsed_url['path'])) {
      $errors[] = $this->t('Path is empty.');
    }

    if (!empty($parsed_url['query'])) {
      $errors[] = $this->t('No query allowed.');
    }

    if (!parse_url('internal:/' . $path)) {
      $errors[] = $this->t('Invalid path. Valid characters are alphanumerics as well as "-", ".", "_" and "~".');
    }

    $path_sections = explode('/', $path);
    // Symfony routing does not allow to use numeric placeholders.
    // @see \Symfony\Component\Routing\RouteCompiler
    $numeric_placeholders = array_filter($path_sections, function ($section) {
      return (preg_match('/^%(.*)/', $section, $matches)
        && is_numeric($matches[1]));
    });
    if (!empty($numeric_placeholders)) {
      $errors[] = $this->t("Numeric placeholders may not be used. Please use plain placeholders (%).");
    }
    return $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $errors = parent::validate();

    $errors += $this->validatePath($this->getOption('path'));

    return $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoutedDisplay() {
    // No routed display exists, so return NULL
    return NULL;
  }

  /**
   * Block views use exposed widgets only if AJAX is set.
   */
  public function usesExposed() {
    if (!isset($this->has_exposed)) {
      foreach ($this->handlers as $type => $value) {
        foreach ($this->view->$type as $handler) {
          if ($handler->canExpose() && $handler->isExposed()) {
            // One is all we need; if we find it, return TRUE.
            $this->has_exposed = TRUE;
            return TRUE;
          }
        }
      }
      $pager = $this->getPlugin('pager');
      if (isset($pager) && $pager->usesExposed()) {
        $this->has_exposed = TRUE;
        return TRUE;
      }
      $this->has_exposed = FALSE;
    }

    return $this->has_exposed;
  }


}
