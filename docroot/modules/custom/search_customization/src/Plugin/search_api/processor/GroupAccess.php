<?php

namespace Drupal\search_customization\Plugin\search_api\processor;


use Drupal\Core\Annotation\Translation;
use Drupal\ggroup\GroupHierarchyManager;
use Drupal\group\Access\GroupAccessResult;
use Drupal\group\Entity\Group;
use Drupal\search_api\Annotation\SearchApiProcessor;
use Drupal\search_api\Query\Condition;
use Drupal\Core\Session\AccountInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Plugin\search_api\processor\ContentAccess;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\SearchApiException;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds content access checks for group.
 *
 * @SearchApiProcessor(
 *   id = "group_access",
 *   label = @Translation("Group access"),
 *   description = @Translation("Check access for group"),
 *   stages = {
 *     "add_properties" = 0,
 *     "pre_index_save" = -10,
 *     "preprocess_query" = -30,
 *   },
 * )
 */
class GroupAccess extends ContentAccess {
  /**
   * {@inheritdoc}
   */
  public static function supportsIndex(IndexInterface $index) {
    foreach ($index->getDatasources() as $datasource) {
      if ('group' == $datasource->getEntityTypeId()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function preIndexSave() {}

  /**
   * {@inheritdoc}
   */
  public function preprocessSearchQuery(QueryInterface $query) {
    if (!$query->getOption('search_api_bypass_access')) {
      $account = $query->getOption('search_api_access_account', $this->getCurrentUser());

      if (is_numeric($account)) {
        $account = User::load($account);
      }

      if ($account instanceof AccountInterface) {
        try {
          $this->addGroupAccess($query, $account);
        }
        catch (SearchApiException $e) {
          $this->logException($e);
        }
      }
      else {
        $account = $query->getOption('search_api_access_account', $this->getCurrentUser());

        if ($account instanceof AccountInterface) {
          $account = $account->id();
        }

        if (!is_scalar($account)) {
          $account = var_export($account, TRUE);
        }

        $this->getLogger()->warning('An illegal user UID was given for group access: @uid.', ['@uid' => $account]);
      }
    }
  }

  /**
   * Adds a group access filter to a search query.
   *
   * @param QueryInterface $query
   *   The query to which a group access filter should be added.
   * @param AccountInterface $account
   *   The user for whom the search is executed.
   *
   * @throws SearchApiException
   *   Thrown if not all necessary fields are indexed on the index.
   */
  protected function addGroupAccess(QueryInterface $query, AccountInterface $account) {
    // Don't do anything if the user can access all content.
    if ($account->hasPermission('bypass group access')) {
      return;
    }

    $condition_group = $query->getConditionGroup();
    $fields = $query->getIndex()->getFields();
    $group_types = ['project ', 'region', 'product', 'knowledge_vault'];
    $ga_gid = [];

    if (isset($fields['field_group_status'])) {
      foreach ($condition_group->getConditions() as &$condition) {
        $conditions = &$condition->getConditions();

        foreach ($conditions as $key => $search_condition) {
          if (
            $search_condition instanceof Condition &&
            'search_api_datasource' == $search_condition->getField() &&
            'entity:group' == $search_condition->getValue()
          ) {
            $custom_condition = $query->createConditionGroup();
            $custom_condition->addCondition($search_condition->getField(), $search_condition->getValue());

            $governance_area =  \Drupal::entityTypeManager()
              ->getStorage('group')
              ->loadByProperties(['type' => 'governance_area']);

            if (!empty($governance_area)) {
              /* @var GroupHierarchyManager $hierarchy_manager*/
              $hierarchy_manager = \Drupal::service('ggroup.group_hierarchy_manager');

              /* @var Group $group */
              foreach ($governance_area as $group) {
                if (!GroupAccessResult::allowedIfHasGroupPermission($group, $account, 'view group')->isAllowed()) {
                  $ga_gid[] = $group->id();
                }

                $ga_gid = array_merge($ga_gid, $hierarchy_manager->getGroupSubgroupIds($group));
              }

              if (empty($ga_gid)) {
                $group_types[] = 'governance_area';
              }
            }

            $bundle_condition = $query->createConditionGroup('OR');

            $access_condition = $query->createConditionGroup();
            $access_condition->addCondition('type_1', $group_types, 'IN');
            $access_condition->addCondition('id', $ga_gid, 'NOT IN');

            $bundle_condition->addConditionGroup($access_condition);

            $publish_condition = $query->createConditionGroup();
            $publish_condition->addCondition('type_1', 'country');
            $publish_condition->addCondition('field_group_status', 'published');

            $bundle_condition->addConditionGroup($publish_condition);
            $custom_condition->addConditionGroup($bundle_condition);
            $condition->addConditionGroup($custom_condition);

            unset($conditions[$key]);
          }
        }
      }
    }
  }

}
