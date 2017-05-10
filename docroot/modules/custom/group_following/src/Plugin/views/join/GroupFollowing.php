<?php

namespace Drupal\group_following\Plugin\views\join;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\group_following\GroupFollowingStorageInterface;
use Drupal\group_following\Helper\Sql\Builder;
use Drupal\views\Plugin\views\join\JoinPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @ingroup views_join_handlers
 * @ViewsJoin("group_following")
 */
class GroupFollowing extends JoinPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Helper service
   * @var \Drupal\group_following\Helper\Sql\Builder
   */
  protected $sqlBuilder;

  /**
   * @var GroupFollowingStorageInterface
   */
  protected $groupFollowingStorage;

  /**
   * GroupFollowing constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\group_following\GroupFollowingStorageInterface $group_following_storage
   * @param \Drupal\group_following\Helper\Sql\Builder $sql_builder
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GroupFollowingStorageInterface $group_following_storage, Builder $sql_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->groupFollowingStorage = $group_following_storage;
    $this->sqlBuilder = $sql_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('group_following.storage'),
      $container->get('group_following.builder_sql')
    );
  }

  /**
   * Builds the SQL for the join this object represents.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $select_query
   *   The select query object.
   * @param string $table
   *   The base table to join.
   * @param \Drupal\views\Plugin\views\query\QueryPluginBase $view_query
   *   The source views query.
   */
  public function buildJoin($select_query, $table, $view_query) {
    $this->groupFollowingStorage->buildJoin($this, $select_query, $table, $view_query, $this->configuration['type']);
  }

}
