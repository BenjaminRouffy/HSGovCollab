<?php

/**
 * Views alteration.
 */

/**
 * Implements hook_views_data_alter().
 */
function group_following_views_data_alter(array &$data) {

  $data['node_field_data']['global_content']['filter']['id'] = 'boolean_string2';
  $data['node_field_data']['global_content']['filter']['accept_null'] = TRUE;

  $data['groups_field_data']['group_following'] = array(
    'title' => t('Group following'),
    'help' => t('Group following'),
    'relationship' => array(
      'base field' => 'end_vertex',
      'join_id' => 'group_following',
      'id' => 'group_following',
      'label' => t('Group following label for relationship'),
      'base' => 'group_graph',
      'field' => 'id',
    ),
  );
  $data['group_content_field_data']['group_following'] = array(
    'title' => t('Group following'),
    'help' => t('Group following'),
    'relationship' => array(
      'base field' => 'end_vertex',
      'join_id' => 'group_following',
      'id' => 'group_following',
      'label' => t('Group following label for relationship'),
      'base' => 'group_graph',
      'field' => 'gid',
    ),
  );
}
