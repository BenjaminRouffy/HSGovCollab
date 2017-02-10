<?php

/**
 * Views alteration.
 */

/**
 * Implements hook_views_data_alter().
 */
function ggroup_views_data_alter(array &$data) {
  $entity_type_manager = \Drupal::entityTypeManager();
  $entity_types = $entity_type_manager->getDefinitions();

  /** @var \Drupal\group\Plugin\GroupContentEnablerManagerInterface $plugin_manager */
  $plugin_manager = \Drupal::service('plugin.manager.group_content_enabler');

  // Add views data for all defined plugins so modules can provide default
  // views even though their plugins may not have been installed yet.
  foreach ($plugin_manager->getAll() as $plugin_id => $plugin) {
    $entity_type_id = $plugin->getEntityTypeId();
    $entity_type = $entity_types[$entity_type_id];
    $entity_data_table = $entity_type->getDataTable() ?: $entity_type->getBaseTable();

    // We only add one 'group_content' entry per entity type.
    if (isset($data[$entity_data_table]['group_content'])) {
      continue;
    }

    $data[$entity_data_table]['group_id_depth'] = array(
      'help' => t('Group id with depth implemented by subgroups'),
      'argument' => array(
        'title' => t('Has parent group ID (with depth)'),
        'id' => 'group_id_depth',
      ),
    );
  }
}
