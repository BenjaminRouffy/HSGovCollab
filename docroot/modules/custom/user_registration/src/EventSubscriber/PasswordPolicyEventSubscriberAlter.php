<?php

namespace Drupal\user_registration\EventSubscriber;

use Drupal\Core\Url;
use Drupal\password_policy\EventSubscriber\PasswordPolicyEventSubscriber;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use \Drupal\user\Entity\User;

/**
 * Enforces password reset functionality.
 */
class PasswordPolicyEventSubscriberAlter extends PasswordPolicyEventSubscriber {

  /**
   * {@inheritdoc}
   */
  public function checkForUserPasswordExpiration(GetResponseEvent $event) {
    $user = \Drupal::currentUser();
    // There needs to be an explicit check for non-anonymous or else
    // this will be tripped and a forced redirect will occur.
    if ($user->id() > 0) {
      /* @var UserInterface $user */
      $user = User::load($user->id());
      $route_name = \Drupal::routeMatch()->getRouteName();

      // system/ajax.
      $ignored_routes = [
        'entity.user.edit_form',
        'system.ajax',
        'user.logout',
        'page_manager.page_view_my_settings',
        'page_manager.page_view_page_403',
        'page_manager.page_view_page_404',
      ];

      $user_expired = FALSE;
      $password_expiration = $user->get('field_password_expiration')->get(0);

      if (!empty($password_expiration)) {
        $user_expired = $password_expiration->getValue()['value'];
      }

      if (!empty($user_expired) and !in_array($route_name, $ignored_routes)) {
        if (!$user->hasPermission('access administration pages')) {
          $url = new Url('page_manager.page_view_my_settings');
        }
        else {
          $url = new Url('entity.user.edit_form', ['user' => $user->id()]);
        }

        $url = $url->setAbsolute(TRUE)->toString();
        $event->setResponse(new RedirectResponse($url));
        drupal_set_message('Your password has expired, please update it', 'error');
      }
    }
  }

}
