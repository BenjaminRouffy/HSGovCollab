<?php

namespace Drupal\sdk\Controller;

// Core components.
use Drupal\Core\Controller\ControllerBase;
// Symfony components.
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SdkController.
 */
class SdkController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function callback($sdk) {
    try {
      $redirect = sdk_deriver($sdk)->loginCallback();

      if ($redirect instanceof RedirectResponse) {
        return $redirect;
      }
    }
    catch (\Exception $e) {
      $this->getLogger('sdk')->notice($e->getMessage());
    }

    if (empty($_SESSION['destination'])) {
      $destination = $GLOBALS['base_url'];
    }
    else {
      $destination = $_SESSION['destination'];
      unset($_SESSION['destination']);
    }

    return new RedirectResponse($destination);
  }

}
