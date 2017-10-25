<?php

namespace Drupal\events\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

class ThemeSwitcher implements ThemeNegotiatorInterface {

  /**
   * @inheritdoc
   */
  public function applies(RouteMatchInterface $route_match) {
    $routes = array('node.add', 'entity.node.edit_form', 'entity.group_content.create_form');
    if (in_array($route_match->getRouteName(), $routes)) {
      $parameters = $route_match->getParameter('node_type');
      $user = \Drupal::currentUser();

      $user_roles = $user->getRoles();
      if (in_array('co_admin', $user_roles)) {
        // If user is Co-Admin should use only administration theme.
        return FALSE;
      }

      if ($route_match->getRouteName() == 'entity.group_content.create_form') {
        $group = $route_match->getParameter('group');
        $plugin_id = $route_match->getParameter('plugin_id');
        if ($plugin_id) {
          $plugin = explode(':', $plugin_id);
          if (!empty($plugin)) {
            $node_type = $plugin[1];
          }
        }
      }
      else {
        $node_type = $parameters->get('type');
      }

      // Node types which should be displayed on different theme.
      $types = array(
        'event',
        'news',
        'document',
      );

      if (in_array($node_type, $types)) {
        // If the group is set, assume that the user is accessing the form
        // from group page (calendar, news, etc.).
        if ($group) {
          $member = $group->getMember($user);
          $member_roles = $member->getRoles();
          foreach ($member_roles as $role) {
            if ($role->get('label') === 'Manager') {
              // if the user is manager in the group let him
              return FALSE;
            }
          }
          // Let the user user the Ample theme.
          return TRUE;
        }
        else {
          // If there is no group user should use only the Ample theme.
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'ample';
  }
}