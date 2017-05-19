<?php

namespace Drupal\events\Plugin\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\Routing\Route;

/**
 * Class CalendarAccessCheck.
 *
 * @package Drupal\events
 */
class CalendarExportAccessCheck implements AccessInterface {

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * @var \Drupal\events\Plugin\Access\CalendarAccessCheck
   */
  protected $calendarAccessCheck;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new CalendarAccessCheck object.
   */
  public function __construct(AccountInterface $account, CalendarAccessCheck $calendarAccessCheck, EntityManagerInterface $entityManager) {
    $this->account = $account;
    $this->calendarAccessCheck = $calendarAccessCheck;
    $this->entityManager = $entityManager;
  }

  /**
   * A custom access check.
   *
   * @param \Symfony\Component\Routing\Route $route
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @param $arg_0
   *
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultNeutral
   */
  public function access(Route $route, RouteMatchInterface $route_match, $arg_0) {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = entity_load('group', $arg_0);
    if ($group instanceof GroupInterface) {
      return $this->calendarAccessCheck->access($route, $route_match, $group);
    }
    return AccessResult::neutral();
  }

}
