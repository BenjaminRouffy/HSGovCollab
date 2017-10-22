<?php

namespace Drupal\events\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\NodeType;

/**
 * Provides a 'AddEventForm' block.
 *
 * @Block(
 *  id = "add_event_form",
 *  admin_label = @Translation("Add Event popup form"),
 * )
 */
class AddEventForm extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $form = $this->getEventPopupForm();

    $build['add_event_form']['#markup'] = render($form);

    return $build;
  }

  /**
   * Helper function for getting the form and adjusting form elements.
   *
   * @return $this|array
   */
  private function getEventPopupForm() {
    $route = \Drupal::routeMatch()->getRouteName();
    $display_fields = array();

    // Array with the fields on the form.
    $fields = array(
      'body',
      'field_category',
      'field_comments',
      'field_date',
      'field_documents',
      'field_event_author',
      'field_event_link',
      'field_event_slider',
      'field_location',
      'field_organization',
      'field_related_content_selector',
      'field_timezone',
      'title',
      'field_join_block',
      'langcode',
      'field_add_event_in_group',
    );

    if($route === 'group.calendar') {
      // Get the group from the URL.
      $group = \Drupal::request()->get('group');

      // Get the for with group functionality.
      $form = \Drupal::service('events.add_event')->createForm($group, 'group_node:event');

      // Array with the fields to be displayed on the form.
      $display_fields = array(
        'title',
        'field_event_slider',
        'body',
        'field_date',
        'field_documents',
      );
    }
    elseif ($route === 'page_manager.page_view_my_calendar') {
      $type = NodeType::load('event');
      $node = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->create(array('type' => $type->id()));

      $form = \Drupal::entityTypeManager()->getFormObject('node', 'default')->setEntity($node);
      $form = \Drupal::formBuilder()->getForm($form);

      // Array with the fields to be displayed on the form.
      $display_fields = array(
        'title',
        'field_event_slider',
        'body',
        'field_date',
        'field_documents',
        'field_add_event_in_group',
      );
    }

    $form['advanced']['#access'] = FALSE;

    // Remove grouping on the form.
    $form['#group_children'] = array();

    // Hide the field that are not in the display_fields array.
    foreach ($form as $key => $value) {
      if (in_array($key, $fields)) {
        if (!in_array($key, $display_fields)) {
          $form[$key]['#access'] = FALSE;
        }
      }
    }

    return $form;
  }

}
