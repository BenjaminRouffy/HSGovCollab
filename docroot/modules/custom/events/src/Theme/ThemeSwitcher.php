<?php

namespace Drupal\events\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\group\Entity\Group;

class ThemeSwitcher implements ThemeNegotiatorInterface {

  /**
   * @inheritdoc
   */
  public function applies(RouteMatchInterface $route_match) {
    $routes = array('node.add', 'entity.node.edit_form', 'entity.node.delete_form', 'entity.group_content.create_form');
    if (in_array($route_match->getRouteName(), $routes)) {
      $node_type_parameter = $route_match->getParameter('node_type');
      $node_parameter = $route_match->getParameter('node');
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
        if ($node_type_parameter) {
          $node_type = $node_type_parameter->get('type');
        }
        elseif (isset($node_parameter)) {
          $node_type = $node_parameter->getType();
        }
        else {
          return FALSE;
        }
      }

      // Node types which should be displayed on different theme.
      $types = array(
        'event',
        'news',
        'document',
        'article',
      );

      if (in_array($node_type, $types)) {
        // If the group is set, assume that the user is accessing the form
        // from group page (calendar, news, etc.).
        if (isset($group)) {
          $member = $group->getMember($user);
          if ($member) {
            $member_roles = $member->getRoles();
            foreach ($member_roles as $role) {
              if ($role->get('label') === 'Manager') {
                // if the user is manager in the group let him
                return FALSE;
              }
            }
          }
          else {
            $manager = \Drupal::service('ggroup.group_hierarchy_manager');
            $groups = $manager->getGroupSupergroups($group->id());

            if (!empty($groups)) {
              foreach ($groups as $group) {
                $memberships = $group->getMember($user);

                if ($memberships) {
                  foreach ($memberships->getRoles() as $role) {
                    if ($role->get('label') === 'Manager') {
                      // If the user is manager in the group let him
                      return FALSE;
                    }
                  }
                }
              }
              // If user is not member of the parent groups
              // should use the Ample theme for adding content.
              return TRUE;
            }
            else {
              // Let the user user the Ample theme.
              return TRUE;
            }
          }
        }
        else {
          if (isset($node_parameter)) {
            $query = \Drupal::database()->select('group_content_field_data', 'gcfd');
            $query->fields('gcfd', ['gid']);
            $query->condition('entity_id', $node_parameter->id(), '=');
            $results = $query->execute()->fetchAll();

            if (!empty($results)) {
              foreach ($results as $key => $result) {
                $group = Group::load($result->gid);
                if (isset($group)) {
                  $member = $group->getMember($user);
                  if ($member) {
                    $member_roles = $member->getRoles();
                    foreach ($member_roles as $role) {
                      if ($role->get('label') === 'Manager') {
                        // if the user is manager in the group let him
                        return FALSE;
                      }
                    }
                  }
                  else {
                    $manager = \Drupal::service('ggroup.group_hierarchy_manager');
                    $groups = $manager->getGroupSupergroups($group->id());

                    if (!empty($groups)) {
                      foreach ($groups as $group) {
                        $memberships = $group->getMember($user);

                        if ($memberships) {
                          foreach ($memberships->getRoles() as $role) {
                            if ($role->get('label') === 'Manager') {
                              // If the user is manager in the group let him
                              return FALSE;
                            }
                          }
                        }
                      }
                      // If user is not member of the parent groups
                      // should use the Ample theme for adding content.
                      return TRUE;
                    }
                    else {
                      // Let the user user the Ample theme.
                      return TRUE;
                    }
                  }
                }
              }
            }
            else {
              // Let the user user the Ample theme.
              return TRUE;
            }
          }
          else {
            // If there is no group or node user should use the Ample theme.
            return TRUE;
          }
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