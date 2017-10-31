<?php

namespace Drupal\country;

use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserData;
use Drupal\user\UserDataInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class CountryCollapsibleService.
 *
 * @package Drupal\country
 */
class CountryCollapsibleService {

  const UNDEFINED = 'undefined';

  const COLLAPSED = 'collapsed';

  const UNCOLLAPSED = 'uncollapsed';

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\user\UserData definition.
   *
   * @var \Drupal\user\UserData
   */
  protected $userData;

  /**
   * Constructs a new CountryCollapsibleService object.
   */
  public function __construct(AccountProxyInterface $current_user, UserDataInterface $user_data) {
    $this->currentUser = $current_user;
    $this->userData = $user_data;
    $this->uid = $this->currentUser->getAccount()->id();
  }

  /**
   * {@inheritdoc}
   */
  public function isCollapsedText($gid) {
    $text = $this->isCollapsed($gid) ? self::COLLAPSED : self::UNCOLLAPSED;
    return new HtmlEscapedText($text);
  }

  /**
   * {@inheritdoc}
   */
  private function isCollapsed($gid) {
    return $this->getValue($gid);
  }

  /**
   * {@inheritdoc}
   */
  public function toggle($group) {
    if ($this->isCollapsed($group->id())) {
      $this->uncollapse($group->id());
    }
    else {
      $this->collapse($group->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  private function uncollapse($id) {
    $this->setValue($id, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  private function collapse($id) {
    $this->setValue($id, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  private function setValue($id, $value) {
    $this->userData->set('country', $this->uid, "collapsed_$id", $value);
  }

  /**
   * {@inheritdoc}
   */
  private function getValue($id) {
    return $this->userData->get('country', $this->uid, "collapsed_$id") ?: FALSE;
  }

}
