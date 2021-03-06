<?php

/**
 * @file
 * Contains \Drupal\page_manager\EventSubscriber\RouteParamContext.
 */

namespace Drupal\group_parent_context\EventSubscriber;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group\Entity\GroupContent;
use Drupal\node\Entity\Node;
use Drupal\page_manager\Event\PageManagerContextEvent;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\page_manager\Event\PageManagerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Sets values from the route parameters as a context.
 */
class ParentGroupsContext implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new CurrentUserContext.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RouteProviderInterface $route_provider, RequestStack $request_stack) {
    $this->routeProvider = $route_provider;
    $this->requestStack = $request_stack;
  }

  /**
   * Adds parents groups as context.
   * @param \Drupal\page_manager\Event\PageManagerContextEvent $event
   *   The page entity context event.
   */
  public function onPageContext(PageManagerContextEvent $event) {
    $request = $this->requestStack->getCurrentRequest();
    $page = $event->getPage();
    $routes = $this->routeProvider->getRoutesByPattern($page->getPath())->all();
    $route = reset($routes);

    if ($route && $route_contexts = $route->getOption('parameters')) {
      foreach ($route_contexts as $route_context_name => $route_context) {
        if ($route_context_name == 'node') {
          if ($request->attributes->has('node')) {
            $node = $request->attributes->get($route_context_name);
            $value = [];

            if (is_numeric($node)) {
              $node = Node::load($node);
            }

            $group_content_array = GroupContent::loadByEntity($node);

            foreach ($group_content_array as $group_content) {
              $value[] = $group_content->getGroup();
            }
          }
          else {
            $value = NULL;
          }

          $cacheability = new CacheableMetadata();
          $cacheability->setCacheContexts(['route']);

          $context = new Context(new ContextDefinition('entity:group', $this->t('Parent groups'), FALSE, TRUE), $value);
          $context->addCacheableDependency($cacheability);

          $page->addContext('parent_groups', $context);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[PageManagerEvents::PAGE_CONTEXT][] = 'onPageContext';
    return $events;
  }

}
