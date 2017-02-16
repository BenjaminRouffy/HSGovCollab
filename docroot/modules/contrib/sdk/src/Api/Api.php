<?php

namespace Drupal\sdk\Api;

use Drupal\sdk\Entity\Sdk;

/**
 * Class Api.
 */
abstract class Api {

  /**
   * SDK configuration.
   *
   * @var Sdk
   */
  protected $entity;

  /**
   * Api constructor.
   *
   * @param Sdk $entity
   *   SDK configuration.
   */
  public function __construct(Sdk $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->entity;
  }

}
