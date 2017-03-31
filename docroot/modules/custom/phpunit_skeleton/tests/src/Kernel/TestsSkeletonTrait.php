<?php

namespace Drupal\Tests\phpunit_skeleton\Kernel;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Entity\Storage\GroupContentTypeStorageInterface;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\user\Entity\User;

/**
 * PHPUnit skeleton
 */
trait TestsSkeletonTrait {
  use NodeCreationTrait {
    getNodeByTitle as drupalGetNodeByTitle;
    createNode as drupalCreateNode;
  }

  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Default user account.
   *
   * @var User
   */
  private $defaultAccount;

  /**
   * Super admin account.
   *
   * @var User
   */
  private $superAccount;

  function constructDefaultSkeleton($with_super_user = TRUE) {
    // Create the test user account.
    $this->defaultAccount = $this->createUser(['uid' => 2]);

    if ($with_super_user) {
      $this->superAccount = $this->createUser(['uid' => 1]);
    }

    if (!isset($this->entityTypeManager)) {
      $this->entityTypeManager = $this->container->get('entity_type.manager');
    }
  }

  /**
   * Generate content types.
   *
   * @param string $type_id
   *   Content type ID.
   *
   * @return NodeType
   *   Created content type.
   */
  private function generateNodeTypeSkeleton($type_id = 'page') {
    $this->installConfig(['node']);
    $this->installSchema('node', ['node_access']);

    // Create a node type.
    $content_type = $this->drupalCreateContentType([
      'type' => $type_id,
      'name' => $this->randomMachineName(),
      'display_submitted' => FALSE,
    ]);

    // Enable two additional languages.
    ConfigurableLanguage::createFromLangcode('de')->save();
    ConfigurableLanguage::createFromLangcode('it')->save();

    return $content_type;
  }

  /**
   * Init group modules and import default config.
   */
  private function initGroupFunctionality() {
    $this->installConfig(['group', 'group_test_config']);
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group_content');
    $this->installEntitySchema('group_content_type');
  }

  /**
   * Generate group types.
   *
   * @param string $type_id
   *   Group type ID.
   *
   * @return GroupTypeInterface
   *   Created content type.
   */
  private function generateGroupTypeSkeleton($type_id = 'country') {
    /* @var GroupTypeInterface $group_type */
    $group_type = $this->entityTypeManager->getStorage('group_type')
      ->create([
        'id' => $type_id,
        'label' => $this->randomMachineName(),
        'description' => ''
      ]);

    $this->entityTypeManager->getStorage('group_type')->resetCache();

    return $group_type;
  }

  /**
   * Return test group type.
   *
   * @return mixed
   *  Group type.
   */
  public function getDefaultGroupType() {
    return $this->entityTypeManager->getStorage('group_type')->load('default');
  }

  private function attachContentTypeToGroup(GroupType $group_type, $content_type_id) {
    /** @var GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    $storage->createFromPlugin($group_type, 'group_node:' . $content_type_id)->save();
  }

  /**
   * Creates a group by type.
   *
   * @param string $type
   *   Group type.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return Group
   *   The created group entity.
   */
  protected function createGroupByType($type, $values = []) {
    /* @var Group $group */
    $group = $this->entityTypeManager->getStorage('group')->create($values + [
      'type' => $type,
      'label' => $this->randomMachineName(),
    ]);

    $group->enforceIsNew();
    $group->save();

    return $group;
  }

}
