<?php

namespace Drupal\country\EventSubscriber;

use Drupal\user\UserDataInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class CollapsibleSubscriber.
 *
 * @package Drupal\country
 */
class CollapsibleSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\user\UserDataInterface definition.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * Constructs a new CollapsibleSubscriber object.
   *
   * @param \Drupal\user\UserDataInterface $country_user_context
   */
  public function __construct(UserDataInterface $country_user_context) {
    $this->userData = $country_user_context;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE] = ['onResponse'];
    return $events;
  }

  /**
   * This method is called whenever the kernel.response event is
   * dispatched.
   */
  public function onResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    if (isset($this->userData->cookiesUpdate) && !empty($this->userData->cookiesUpdate)) {
      foreach ($this->userData->cookiesUpdate as $item) {
        $response->headers->setCookie($item);
      }
    }
  }

}
