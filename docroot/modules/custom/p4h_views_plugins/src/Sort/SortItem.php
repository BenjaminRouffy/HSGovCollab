<?php

namespace Drupal\p4h_views_plugins\Sort;

use Drupal\group\Entity\Group;

class SortItem {
  /**
   * Target group.
   *
   * @var \Drupal\group\Entity\Group
   */
  protected $group;

  /**
   * The date format storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $label;

  function __construct(Group $group) {
    $this->group = $group;
    $this->label = \Drupal::entityManager()
      ->getTranslationFromContext($group)
      ->label();
  }

  /**
   * @return mixed
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @return mixed
   */
  public function getGroup() {
    return $this->group;
  }
}
