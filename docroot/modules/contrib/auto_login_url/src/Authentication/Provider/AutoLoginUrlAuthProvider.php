<?php

namespace Drupal\auto_login_url\Authentication\Provider;

use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AutoLoginUrlAuthProvider.
 *
 * @package Drupal\auto_login_url\Authentication\Provider
 */
class AutoLoginUrlAuthProvider implements AuthenticationProviderInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RouteMatch interface.
   *
   * @var \Drupal\auto_login_url\Authentication\Provider\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a HTTP basic authentication provider object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Checks whether suitable authentication credentials are on the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return bool
   *   TRUE if authentication credentials suitable for this provider are on the
   *   request, FALSE otherwise.
   */
  public function applies(Request $request) {
    return ($request->query->has('provider_access') && $request->query->has('provider_access'));
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {

    if($request->query->has('provider_access') && $request->query->has('provider_access')) {

      // Check hash is unique.
      $result = \Drupal::service('auto_login_url.login')->getUserByHash(
        $request->query->get('provider_user'),
        $request->query->get('provider_access')
      );

      if (count($result) > 0 && isset($result['uid'])) {
        return User::load($result['uid']);
      }
    }
    throw new AccessDeniedHttpException();
  }

}
