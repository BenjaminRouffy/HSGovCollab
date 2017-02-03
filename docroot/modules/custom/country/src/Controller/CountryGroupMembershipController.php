<?php

namespace Drupal\country\Controller;

use Drupal\group\Controller\GroupMembershipController;
use Drupal\group\Entity\GroupInterface;
use Drupal\Component\Utility\Xss;

/**
 * Provides group membership route controllers.
 */
class CountryGroupMembershipController extends GroupMembershipController {

  /**
   * The _title_callback for the join form route.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to join.
   *
   * @return string
   *   The page title.
   */
  public function groupTitle(GroupInterface $group) {
    return ['#markup' => $group->label(), '#allowed_tags' => Xss::getHtmlTagList()];
  }

}
