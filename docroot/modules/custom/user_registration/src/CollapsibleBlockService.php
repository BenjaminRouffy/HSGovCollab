<?php

namespace Drupal\user_registration;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserDataInterface;

/**
 * Class CollapsibleBlockService.
 */
class CollapsibleBlockService {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\user\UserData
   */
  protected $userData;

  /**
   * Constructs a new CollapsibleBlockService object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   */
  public function __construct(AccountProxyInterface $current_user, UserDataInterface $user_data) {
    $this->currentUser = $current_user;
    $this->userData = $user_data;
  }

  /**
   * Determines if block is collapsed.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   Block content.
   *
   * @return bool
   *   TRUE if block is collapsed.
   */
  public function isCollapsed(BlockContent $block) {
    return $this->getValue($block->id());
  }

  /**
   * Determines if block is expanded.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   Block content.
   *
   * @return bool
   *   TRUE if block is expanded.
   */
  public function isExpanded(BlockContent $block) {
    return !$this->getValue($block->id());
  }

  /**
   * Display or hide the block content.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   Block content to toggle.
   * @param bool $display
   *   Use true to show the block or false to hide it.
   */
  public function toggle(BlockContent $block, $display = NULL) {
    if (is_null($display)) {
      $display = $this->isCollapsed($block);
    }
    $display ? $this->expand($block) : $this->collapse($block);
  }

  /**
   * Expand the block content.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   Block content to expand.
   */
  protected function expand(BlockContent $block) {
    $this->setValue($block->id(), FALSE);
  }

  /**
   * Collapse the block content.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   Block content to collapse.
   */
  protected function collapse(BlockContent $block) {
    $this->setValue($block->id(), TRUE);
  }

  /**
   * Sets block content collapsible param for the user.
   *
   * @param int $block_id
   *   Block content id to set collapse param for.
   * @param bool $value
   *   Use true for collapsed block or false for expanded.
   */
  private function setValue($block_id, $value) {
    $this->userData->set('user_registration', $this->currentUser->id(), "collapsed_block_$block_id", $value);
  }

  /**
   * Reads block content collapsible param for the user.
   *
   * @param $block_id
   *   Block content id.
   *
   * @return bool
   *   TRUE if block is collapsed, FALSE otherwise.
   */
  private function getValue($block_id) {
    return (bool) $this->userData->get('user_registration', $this->currentUser->id(), "collapsed_block_$block_id");
  }

}
