<?php

namespace Drupal\social_block\SocialNetwork;

/**
 * Class ContentItem.
 */
class ContentItem {

  /**
   * Text of a post.
   *
   * @var string
   */
  private $text = '';
  /**
   * Absolute URL of a post.
   *
   * @var string
   */
  private $link = '';
  /**
   * Absolute URL of image, if it attached to a post.
   *
   * @var string
   */
  private $image = '';
  /**
   * Machine name of a social network from which post has been obtained.
   *
   * @var string
   */
  private $network = '';
  /**
   * UNIX timestamp of a post creation date.
   *
   * @var int
   */
  private $timestamp = 0;

  /**
   * {@inheritdoc}
   */
  public function getText() {
    return $this->text;
  }

  /**
   * {@inheritdoc}
   */
  public function setText($text) {
    $this->text = $text;
  }

  /**
   * {@inheritdoc}
   */
  public function getLink() {
    return $this->link;
  }

  /**
   * {@inheritdoc}
   */
  public function setLink($link) {
    $this->link = $link;
  }

  /**
   * {@inheritdoc}
   */
  public function getImage() {
    return $this->image;
  }

  /**
   * {@inheritdoc}
   */
  public function setImage($image) {
    $this->image = $image;
  }

  /**
   * {@inheritdoc}
   */
  public function getNetwork() {
    return $this->network;
  }

  /**
   * {@inheritdoc}
   */
  public function setNetwork($network) {
    $this->network = $network;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimestamp() {
    return $this->timestamp;
  }

  /**
   * {@inheritdoc}
   */
  public function setTimestamp($timestamp) {
    $this->timestamp = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
  }

  /**
   * Checks item invalidity.
   *
   * @return bool
   *   A state of check.
   */
  public function isInvalid() {
    return (empty($this->text) || empty($this->image)) && empty($this->link) && empty($this->timestamp);
  }

}
