<?php

namespace Drupal\group_following\Helper\Sql;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Connection;

/**
 * Class Builder.
 */
class Builder {

  protected $connection;

  /**
   * Builder constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * The nested query will return all available membership types.
   *
   * In most cases return:
   *
   * @example
   *   country-group_membership
   *   project-group_membership
   *   region-group_membership
   *
   * @todo This part can be optimised.
   *
   * @return array
   *   List of membership types.
   */
  public function getAllGroupMemberShipTypes() {
    $select = $this->connection->select('group_content', 'gc');
    $select->addField('gc', 'type');
    $select->condition('gc.type', '%group_membership', 'LIKE');
    $select->groupBy('gc.type');
    return $select->execute()->fetchCol();
  }

  /**
   * Type field of membership without "group_membership" prefix.
   *
   * @example
   *    Fields will equal "region" for "region-group_membership"
   *
   * @code
   *    substring_index(gcfd.type, '-', 1)
   * @endcode
   *
   * Role field of membership without "region" suffix and add "unfollower"
   * role by default for empty rows.
   *
   * @example
   *    Fields will equal "follower" for "region-follower"
   *
   * @code
   *    substring_index(if(isnull(gcgr.entity_id), replace(gcfd.type, 'group_membership', 'unfollower'), gcgr.group_roles_target_id), '-',-1)
   * @endcode
   *
   * @example
   * SELECT
   *    gcfd.id                            AS id,
   *    gcfd.gid                           AS gid,
   *    gcfd.entity_id                     AS uid,
   *    substring_index(gcfd.type, '-', 1) AS type,
   *    substring_index(
   *      if(
   *        isnull(gcgr.entity_id),
   *          replace(gcfd.type, 'group_membership', 'unfollower'),
   *          gcgr.group_roles_target_id), '-', -1)  AS role
   *    FROM group_content_field_data gcfd
   *      LEFT OUTER JOIN group_content__group_roles gcgr
   *        ON gcfd.id = gcgr.entity_id
   *    WHERE gcfd.type IN ('region-group_membership', ...)
   *    HAVING role in ('follower', 'unfollower')
   *
   * @param array $where
   *   List of membership types.
   *
   * @return \Drupal\Core\Database\Query\Select
   *   Nested query.
   */
  public function getGroupRolesWithGid(array $where) {

    $select = $this->connection->select('group_content_field_data', 'gcfd');
    $unikey = rand(100, 999);
    // Join "follower" role to membership table,
    // means if the user has a membership (which role does not matter)
    // he/she should has "follower" role to be follower in other cases user
    // we consider user is unfollower.
    $select->leftJoin('group_content__group_roles', ' gcgr', 'gcfd.id = gcgr.entity_id AND gcgr.group_roles_target_id LIKE :follower' . $unikey, [':follower' . $unikey => '%-follower']);

    $select->addField('gcfd', 'id', 'id');
    $select->addExpression('substring_index(gcfd.type, \'-\', 1)', 'type');
    $select->addField('gcfd', 'gid', 'gid');

    // @TODO Ensure that 'unfollower' role is needed here.
    $expression = 'substring_index(if(isnull(gcgr.entity_id), replace(gcfd.type, \'group_membership\', \'unfollower\'), gcgr.group_roles_target_id),\'-\', -1)';
    $select->addExpression($expression, 'role');
    $select->addField('gcfd', 'entity_id', 'uid');

    $select->condition('gcfd.type', $where, 'IN');

    // @TODO Looks like a redundant condition.
    /** @var \Drupal\Core\Database\Query\Condition $or */
    //$select->where($expression . ' IN (\'follower\', \'unfollower\')');

    return $select;
  }

  /**
   * The nested query is designed to correct group_graph issue.
   *
   * By default group_graph will not return groups without children.
   * This query add new row for group joined to themself and increase "hops"
   * for everyone else.
   *
   * @example
   * | 279 | 280 | 0 |
   *
   * will be changed to:
   *
   * @example
   *  | 279 | 279 | 0 | - this is new line
   *  | 279 | 280 | 1 | - hops of child has been increase
   *
   * @return \Drupal\Core\Database\Query\Select
   *   Nested query.
   */
  public function getGroupGraphWithOwn() {
    $group_graph_with_own = $this->connection->select('groups', 'gfd');
    $group_graph_with_own->addExpression('ifnull(gg.start_vertex, gfd.id)', 'start_vertex');
    $group_graph_with_own->addExpression('ifnull(gg.end_vertex, gfd.id)', 'end_vertex');
    $group_graph_with_own->addExpression('if(isnull(gg.hops), 0, gg.hops + 1)', 'hops');
    $group_graph_with_own->leftJoin('group_graph', 'gg', 'gfd.id = gg.end_vertex');
    $group_graph_with_own->orderBy('hops');
    return $group_graph_with_own;
  }

  /**
   * Threads regenerating based on query.
   *
   * @param int $iteration
   *   Max depth level.
   *
   * @return string
   *   String to insert into query.
   */
  public function getRoles($iteration) {
    $fields = [];
    for ($i = 1; $i <= $iteration; $i++) {
      switch ($i) {
        case 1:
          // If the first level and group type is "region",
          // assuming that this condition carry out.
          // The "unfollowed" region at first position
          // should return "follower" role for next one group in the depth.
//          $fields[] = "IF(grg{$i}.role = 'unfollower', IF(grg{$i}.type = 'region', 'unfollower:follower', 'unfollower'), grg{$i}.role)";
          $fields[] = "IF(grg{$i}.role = 'unfollower' AND grg{$i}.type = 'region', ':unfollower:follower', CONCAT(':', grg{$i}.role))";
          break;

        default:
          $fields[] = "CONCAT(':', grg{$i}.role)";
      }
    }
    return "CONCAT_WS(':', " . implode(',', $fields) . ")";
//    return "CONCAT_WS(':', ':', " . implode(',', $fields) . ")";
  }

}
