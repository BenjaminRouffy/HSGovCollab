<?php

namespace Drupal\user_registration;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service Provider for password policy.
 */
class UserRegistrationServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('password_policy_event_subscriber');

    $definition->setClass('\Drupal\user_registration\EventSubscriber\PasswordPolicyEventSubscriberAlter');
  }

}
