<?php

namespace Drupal\knowledge_vault\Plugin\views\style;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Annotation\ViewsStyle;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Unformatted style plugin to render rows one after another with no
 * decorations.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "grouping_limit",
 *   title = @Translation("Grouping limit list"),
 *   theme = "views_view_unformatted",
 *   display_types = {"normal"}
 * )
 */
class GroupingLimitStyle extends StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $group_count = count($this->options['grouping']);

    for ($i = 0; $i <= $group_count; $i++) {
      $grouping = !empty($this->options['grouping'][$i]) ? $this->options['grouping'][$i] : [];

      $form['grouping'][$i]['grouping_limit'] = [
        '#type' => 'number',
        '#title' => $this->t('Items to display'),
        '#min' => 1,
        '#max' => 50,
        '#default_value' =>  !empty($grouping['grouping_limit']) ? $grouping['grouping_limit'] : 10,
        '#states' => [
          'invisible' => [
            ':input[name="style_options[grouping][' . $i . '][field]"]' => ['value' => ''],
          ],
        ],
      ];

      $form['grouping'][$i]['grouping_offset'] = [
        '#type' => 'number',
        '#title' => $this->t('Offset'),
        '#min' => 0,
        '#max' => 50,
        '#default_value' => !empty($grouping['grouping_offset']) ? $grouping['grouping_offset'] : 0,
        '#states' => [
          'invisible' => [
            ':input[name="style_options[grouping][' . $i . '][field]"]' => ['value' => ''],
          ],
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   *
   * @see StylePluginBase::renderGroupingSets().
   */
  public function renderGroupingSets($sets) {
    $output = [];
    $theme_functions = $this->view->buildThemeFunctions($this->groupingTheme);

    foreach ($sets as $set) {
      $level = isset($set['level']) ? $set['level'] : 0;

      $row = reset($set['rows']);

      // Render as a grouping set.
      if (is_array($row) && isset($row['group'])) {
        $single_output = [
          '#theme' => $theme_functions,
          '#view' => $this->view,
          '#grouping' => $this->options['grouping'][$level],
          '#rows' => $set['rows'],
        ];
      }
      // Render as a record set.
      else {
        if ($this->usesRowPlugin()) {
          $set['rows'] = array_slice($set['rows'], $this->options['grouping'][$level]['grouping_offset'], $this->options['grouping'][$level]['grouping_limit']);

          foreach ($set['rows'] as $index => $row) {
            $this->view->row_index = $index;
            $set['rows'][$index] = $this->view->rowPlugin->render($row);
          }
        }

        $single_output = $this->renderRowGroup($set['rows']);
      }

      $single_output['#grouping_level'] = $level;
      $single_output['#title'] = $set['group'];
      $output[] = $single_output;

    }

    unset($this->view->row_index);

    return $output;
  }

}
