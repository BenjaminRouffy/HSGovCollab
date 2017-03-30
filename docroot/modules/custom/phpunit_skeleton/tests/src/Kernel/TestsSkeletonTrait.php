<?php

namespace Drupal\Tests\phpunit_skeleton\Kernel;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Entity\Storage\GroupContentTypeStorageInterface;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;

/**
 * dsfsdf
 */
trait TestsSkeletonTrait {
  use NodeCreationTrait {
    getNodeByTitle as drupalGetNodeByTitle;
    createNode as drupalCreateNode;
  }

  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  private $entityTypeManager;
  private $contentType;
  private $default_account;
  private $uid_1_account;

  function constructFullSkeleton() {
    // Create the test user account.
    $this->default_account = $this->createUser(['uid' => 2]);
    $this->uid_1_account = $this->createUser(['uid' => 1]);

    $this->generateNodeSkeleton();
    $this->generateGroupSkeleton();
  }

  /**
   * Generate node types for tests.
   */
  private function generateNodeSkeleton() {
    $this->installConfig(['node']);
    $this->installSchema('node', ['node_access']);

    // Create a node type.
    $this->contentType = $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
    ]);

    // Enable two additional languages.
    ConfigurableLanguage::createFromLangcode('de')->save();
    ConfigurableLanguage::createFromLangcode('it')->save();
  }

  /**
   * Generate group types.
   */
  private function generateGroupSkeleton() {
    \Drupal::service('module_installer')->install(['group']);

    $this->installConfig(['group', 'group_test_config']);
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group_content');
    $this->installEntitySchema('group_content_type');

    /* @var GroupTypeInterface $group_type */
    $group_type = $this->getEntityTypeManager()->getStorage('group_type')->load('default');

    // Install some node types on some group types.
    /** @var GroupContentTypeStorageInterface $storage */
    $storage = $this->getEntityTypeManager()->getStorage('group_content_type');
    $storage->createFromPlugin($group_type, 'group_node:' . $this->contentType->id())->save();

    $outsider_a = [
      'view group_node:'. $this->contentType->id() . ' entity',
    ];
    $group_type->getOutsiderRole()->grantPermissions($outsider_a)->save();
  }

  /**
   * @return mixed
   */
  public function getEntityTypeManager() {
    if (!isset($this->entityTypeManager)) {
      $this->entityTypeManager = $this->container->get('entity_type.manager');
    }
    return $this->entityTypeManager;
  }
}
