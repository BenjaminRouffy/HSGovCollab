<?php

/**
 * Views alteration.
 */

/**
 * Implements hook_views_data_alter().
 */
function group_following_views_data_alter(array &$data) {

  $data['node_field_data']['global_content']['filter']['accept_null'] = TRUE;

  $relationship = array(
    'title' => t('Group following'),
    'help' => t('Group following'),
    'relationship' => array(
      'join_id' => 'group_following',
      'id' => 'group_following',
      'label' => t('Group following label for relationship'),
      'base' => 'group_graph',
    ),
  );

  $data['users_field_data']['group_following'] = array_replace_recursive($relationship, [
    'relationship' => [
      'base field' => 'uid',
      'field' => 'uid',
    ]
  ]);
  $data['groups_field_data']['group_following'] = array_replace_recursive($relationship, [
    'relationship' => [
      'base field' => 'gid',
      'field' => 'id',
    ]
  ]);
  $data['group_content_field_data']['group_following'] = array_replace_recursive($relationship, [
    'relationship' => [
      'base field' => 'gid',
      'field' => 'gid',
    ]
  ]);

  $data['group_graph']['gid'] = [
    'title' => t('Group following GID'),
    'filter' => [
      'id' => 'numeric'
    ],
    'argument' => [
      'id' => 'numeric'
    ],
  ];

  $data['group_graph']['uid'] = [
    'title' => t('Group following UID'),
    'filter' => [
      'id' => 'numeric'
    ],
    'argument' => [
      'id' => 'numeric'
    ],
  ];

}
