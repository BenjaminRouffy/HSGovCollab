<?php

namespace Drupal\events\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Controller routines for AJAX events routes.
 */
class EventsController extends ControllerBase {

  /**
   * Builds ajax response for request event.
   *
   * @param int $nid
   *   Event ID.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The Ajax response.
   */
  public function getEvent($nid) {
    $entity = Node::load($nid);
    $response = '';
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
    $view = $view_builder->view($entity, 'event_ajax_response', \Drupal::languageManager()->getCurrentLanguage()->getId());

    if (!empty($view)) {
      $html = render($view);
      $response = new AjaxResponse();
      $response->addCommand(new HtmlCommand('#event-response', $html));
    }
    return $response;
  }

}
