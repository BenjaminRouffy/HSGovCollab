<?php
/**
 * @TODO What is this class doing???
 */

namespace Drupal\timeline\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphsBehaviorBase;
use Drupal\paragraphs\ParagraphsBehaviorInterface;

/**
 * @ParagraphsBehavior(
 *   id = "time_tine_item_behavior",
 *   label = "TimeLineItemBehavior"
 * )
 */
class TimeLineItemBehavior extends ParagraphsBehaviorBase implements ParagraphsBehaviorInterface {

  /**
   * Gets this plugin's configuration.
   *
   * @return array
   *   An array of this plugin's configuration.
   */
  public function defaultConfiguration() {
    return ['enabled' => TRUE];
  }

  /**
   * Adds a default set of helper variables for preprocessors and templates.
   *
   * This preprocess function is the first in the sequence of preprocessing
   * functions that are called when preparing variables of a paragraph.
   *
   * @param array $variables
   *   An associative array containing:
   *   - elements: An array of elements to display in view mode.
   *   - paragraph: The paragraph object.
   *   - view_mode: The view mode.
   */
  public function preprocess(&$variables) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $paragraph */
    $paragraph = &$variables['paragraph'];
  }

  /**
   * Extends the paragraph render array with behavior.
   *
   * @param array &$build
   *   A renderable array representing the paragraph. The module may add
   *   elements to $build prior to rendering. The structure of $build is a
   *   renderable array as expected by drupal_render().
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display holding the display options configured for the
   *   entity components.
   * @param string $view_mode
   *   The view mode the entity is rendered in.
   *
   * @return array
   *   A render array provided by the plugin.
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode) {

    /** @var \Drupal\Core\Entity\ContentEntityInterface $paragraph */
    $paragraph1 = &$build;
    $build['field_node']['#language'] = 'fr';
  }

}
