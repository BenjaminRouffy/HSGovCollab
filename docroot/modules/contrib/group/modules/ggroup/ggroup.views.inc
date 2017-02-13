<?php

/**
 * Views alteration.
 */

/**
 * Implements hook_views_data_alter().
 */
function ggroup_views_data_alter(array &$data) {
  $data['group_content_field_data']['group_id_depth'] = array(
    'help' => t('Group id with depth implemented by subgroups'),
    'argument' => array(
      'title' => t('Has parent group ID (with depth)'),
      'id' => 'group_id_depth',
    ),
  );
}
