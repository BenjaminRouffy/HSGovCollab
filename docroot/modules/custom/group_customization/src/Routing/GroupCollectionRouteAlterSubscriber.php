<?php
namespace Drupal\group_customization\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class GroupCollectionRouteAlterSubscriber.
 */
class GroupCollectionRouteAlterSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change path '/user/login' to '/login'.
    if ($route = $collection->get('entity.group_content.collection')) {
      $route->addDefaults(['_title_callback' => 'Drupal\group_customization\Routing\GroupCollectionRouteAlterSubscriber::collectionTitle']);
    }
  }

  /**
   * The _title_callback for the entity.group_content.collection route.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to add the group content to.
   *
   * @return string
   *   The page title.
   *
   * @todo Revisit when 8.2.0 is released, https://www.drupal.org/node/2767853.
   */
  public function collectionTitle(GroupInterface $group) {
    return t('Related content for @group', ['@group' => $group->label()]);
  }

}
