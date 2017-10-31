<?php

namespace Drupal\country\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Controller\ControllerBase;
use Drupal\country\CountryCollapsibleService;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CollapsedController.
 *
 * @package Drupal\country\Controller
 */
class CollapsedController extends ControllerBase {

  /**
   * @var \Drupal\country\CountryCollapsibleService
   */
  protected $countryCollapsed;

  /**
   * Constructs a new DefaultController object.
   *
   * @param \Drupal\country\CountryCollapsibleService $country_collapsed
   */
  public function __construct(CountryCollapsibleService $country_collapsed) {
    $this->countryCollapsed = $country_collapsed;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('country.collapsible_display_mode')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function toggle($js, GroupInterface $group) {
    $this->countryCollapsed->toggle($group);
    Cache::invalidateTags($group->getCacheTags());
    if ($js == 'ajax') {
      $response = new AjaxResponse();
      return $response;
    }
    return $this->redirect('entity.group.canonical', ['group' => $group->id()]);
  }

}
