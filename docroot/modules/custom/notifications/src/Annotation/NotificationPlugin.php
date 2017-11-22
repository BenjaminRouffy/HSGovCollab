<?php

namespace Drupal\notifications\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Notification Plugin item annotation object.
 *
 * @see \Drupal\notifications\Plugin\NotificationPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class NotificationPlugin extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * Default notification selector.
   *
   * @var string
   */
  public $selector;

}
