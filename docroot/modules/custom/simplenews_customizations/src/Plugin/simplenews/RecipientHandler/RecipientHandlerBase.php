<?php

namespace Drupal\simplenews_customizations\Plugin\simplenews\RecipientHandler;

use Drupal\simplenews\Plugin\simplenews\RecipientHandler\RecipientHandlerBase as DefaultRecipientHandler;

/**
 * Base class for all Recipient Handler classes.
 *
 * This handler sends a newsletter issue to all subscribers of a given
 * newsletter.
 *
 * @RecipientHandler(
 *   id = "simplenews_all",
 *   title = @Translation("All newsletter subscribers"),
 *   types = {
 *     "default"
 *   }
 * )
 */
class RecipientHandlerBase extends DefaultRecipientHandler {

}
