<?php

namespace Drupal\simplenews_customizations;

/**
 *
 */
trait RecipientHandlerBaseTrait {

  /**
   *
   */
  public function buildRecipientCountQuery($settings = NULL) {
    return $this->buildRecipientQuery($settings)->countQuery();
  }

  /**
   *
   */
  public function count($settings = NULL) {
    return $this->buildRecipientCountQuery($settings)->execute()->fetchField();
  }

}
