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

    $form = $this->getAddEventForm();

    $build['add_event_form']['#markup'] = render($form);

    return $build;
  }

  /**
   * Helper function for getting and customizing the Event add popup form.
   *
   * @return mixed
   */
  private function getAddEventForm() {
    // Get the group from the URL.
    $group = \Drupal::request()->get('group');

    // Get the for with group functionality.
    $form = \Drupal::service('events.add_event')->createForm($group, 'group_node:event');
    $form['advanced']['#access'] = FALSE;

    // Remove grouping on the form.
    $form['#group_children'] = array();

    $route = \Drupal::routeMatch()->getRouteName();

    // Array with the fields to be displayed on the form.
    if($route === 'group.calendar') {
      $display_fields = array(
        'title',
        'field_date',
      );
    }
    elseif ($route === 'page_manager.page_view_my_calendar') {
      $display_fields = array(
        'title',
        'field_date',
        'field_add_event_in_group',
      );
    }

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

    // Hide the field that are not in the display_fields array.
    foreach ($form as $key => $value) {
      if (in_array($key, $fields)) {
        if (!in_array($key, $display_fields)) {
          $form[$key]['#access'] = FALSE;
        }
      }
    }

    $form['field_date']['#weight'] = 30;

    return $form;
  }

}
