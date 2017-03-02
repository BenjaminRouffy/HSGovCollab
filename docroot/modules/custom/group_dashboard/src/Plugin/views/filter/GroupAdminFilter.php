<?php

namespace Drupal\group_dashboard\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filters by admin access.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("group_admin_filter")
 */
class GroupAdminFilter extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = \Drupal::database()->select('group_content_field_data', 'group_content_field_data')
      ->fields('group_content_field_data', ['gid'])
      ->condition('group_content_field_data.type', '%-group_membership', 'LIKE')
      ->condition('group_content_field_data.entity_id', \Drupal::currentUser()->id());

    $query->leftJoin('group_content__group_roles', 'group_content__group_roles', 'group_content__group_roles.entity_id=group_content_field_data.id');
    $result = $query->condition('group_content__group_roles.group_roles_target_id', '%-admin', 'LIKE')
      ->execute()
      ->fetchCol();

    $this->query->addWhere(0, "$this->table.id", $result, 'IN');
  }

}
