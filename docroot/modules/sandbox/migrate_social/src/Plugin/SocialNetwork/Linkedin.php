<?php

namespace Drupal\migrate_social\Plugin\SocialNetwork;

use Drupal\group\Entity\GroupContentType;
use Drupal\plugin_type_example\SandwichBase;
use Drupal\migrate_social\SocialNetworkBase;
use Drupal\views\Views;

/**
 * Provides a twitter migrate plugin
 *
 * @SocialNetwork(
 *   id = "linkedin",
 *   description = @Translation("Twitter migrate plugin.")
 * )
 */
class Linkedin extends SocialNetworkBase {

  /**
   * {@inheritdoc}
   */
  protected function nextSource() {
    $result = $this->instance->setResponseDataType('array')->get('v1/updates', [
      'query' => [
        'count' => 10000,
      ],
    ]);


    if (!empty($result[0]['id'])) {
      $this->iterator = new \ArrayIterator($result);
      return TRUE;
    }

  }

}
