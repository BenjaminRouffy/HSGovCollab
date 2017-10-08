<?php

namespace Drupal\events\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
    $group = \Drupal::request()->get('group');

    $form = \Drupal::service('events.add_event')->createForm($group, 'group_node:event');
    $form['advanced']['#access'] = FALSE;
    $form['#group_children'] = array();

    $display_fields = array(
      'title',
      'field_date',
    );

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
    );

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
