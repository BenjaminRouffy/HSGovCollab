<?php
namespace Drupal\global_tag\Config\Entity;

use Drupal\node\NodeStorage;

class ConfigEntityStorage extends NodeStorage {

  public function countFieldData($storage_definition, $as_bool = FALSE) {

    // The table mapping contains stale data during a request when a field
    // storage definition is added, so bypass the internal storage definitions
    // and fetch the table mapping using the passed in storage definition.
    // @todo Fix this in https://www.drupal.org/node/2705205.

    return FALSE;
  }
}
