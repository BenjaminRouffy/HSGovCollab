<?php

namespace Drupal\group_dashboard\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\group\Entity\Controller\GroupContentController;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupType;
use Drupal\group\Entity\GroupTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for GroupContent routes.
 */
class GroupAdminContentController extends GroupContentController {
  /**
   * {@inheritdoc}
   */
  public function addPage(GroupInterface $group, $create_mode = FALSE) {
    $build = ['#theme' => 'entity_add_list', '#bundles' => []];
    $form_route = $this->addPageFormRoute($group, $create_mode);
    $bundle_names = $this->addPageBundles($group, $create_mode);
    $current_user = \Drupal::currentUser();

    // Set the add bundle message if available.
    $add_bundle_message = $this->addPageBundleMessage($group, $create_mode);
    if ($add_bundle_message !== FALSE) {
      $build['#add_bundle_message'] = $add_bundle_message;
    }

    // Filter out the bundles the user doesn't have access to.
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('group_content');
    foreach ($bundle_names as $plugin_id => $bundle_name) {
      $access = $access_control_handler->createAccess($bundle_name, NULL, ['group' => $group], TRUE);
      if (!$access->isAllowed()) {
        unset($bundle_names[$plugin_id]);
      }
      $this->renderer->addCacheableDependency($build, $access);
    }

    // Redirect if there's only one bundle available.
    $destination = Url::fromRoute('entity.group_content.collection', ['group' => $group->id()]);
    if (count($bundle_names) == 1) {
      reset($bundle_names);
      $route_params = ['group' => $group->id(), 'plugin_id' => key($bundle_names)];
      $url = Url::fromRoute($form_route, $route_params, [
        'absolute' => TRUE,
        'query' => [
          'destination' => $destination->toString(),
        ],
      ]);
      return new RedirectResponse($url->toString());
    }

    // Set the info for all of the remaining bundles.
    // Add query destination to links.
    foreach ($bundle_names as $plugin_id => $bundle_name) {
      $plugin = $group->getGroupType()->getContentPlugin($plugin_id);
      $label = $plugin->getLabel();

      $group_options = array_filter($group->getGroupType()->getThirdPartySetting('group_dashboard', 'access_to_different_entities', []));
      $plugin_type_id = $plugin->getContentTypeConfigId();

      if (in_array($plugin_type_id, $group_options) && !$current_user->hasPermission("access to relate {$plugin_type_id}")) {
        continue;
      }


      if ($plugin->getBaseId() == 'subgroup') {
        $destination = Url::fromRoute('view.subgroups.page_1', ['group' => $group->id()]);
      }

      $build['#bundles'][$bundle_name] = [
        'label' => $label,
        'description' => $plugin->getContentTypeDescription(),
        'add_link' => Link::createFromRoute($label, $form_route, [
          'group' => $group->id(),
          'plugin_id' => $plugin_id
        ],
          ['query' => ['destination' => $destination->toString()]]
        ),
      ];
    }

    // Add the list cache tags for the GroupContentType entity type.
    $bundle_entity_type = $this->entityTypeManager->getDefinition('group_content_type');
    $build['#cache']['tags'] = $bundle_entity_type->getListCacheTags();

    return $build;
  }

  /**
   * Check access to subgroup page.
   *
   * @param GroupInterface $group
   *   Group object.
   *
   * @return AccessResultInterface
   *    An access result.
   */
  public function AccessToCreateContent(GroupInterface $group) {
    $current_user = \Drupal::currentUser();
    $group_type = GroupType::load($group->bundle());

    if ($group_type->getThirdPartySetting('group_dashboard', 'access_to_related_entities_functionality', 0) && !$current_user->hasPermission('access to related entities page')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * Check access to subgroup page.
   *
   * @param GroupInterface $group
   *   Group object.
   *
   * @return AccessResultInterface
   *    An access result.
   */
  public function AccessToCreateDifferentEntities(GroupInterface $group) {
    $parameter = \Drupal::routeMatch()->getRawParameter('plugin_id');
    $current_user = \Drupal::currentUser();

    if (!empty($parameter)) {
      /* @var GroupTypeInterface $group_type */
      $group_type = $group->getGroupType();
      $group_options = array_filter($group_type->getThirdPartySetting('group_dashboard', 'access_to_different_entities', []));

      if ($group_type->hasContentPlugin($parameter)) {
        $plugin_type_id = $group_type->getContentPlugin($parameter)->getContentTypeConfigId();

        if (in_array($plugin_type_id, $group_options) && !$current_user->hasPermission("access to relate {$plugin_type_id}")) {
          return AccessResult::forbidden();
        }
      }
    }

    return AccessResult::allowed();
  }
}
