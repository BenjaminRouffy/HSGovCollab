<?php

namespace Drupal\Tests\country\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\user\Entity\User;

/**
 * Tests next previous functionality.
 *
 * @group Entity
 */
class FileAccessTest extends EntityKernelTestBase {

  use NodeCreationTrait {
    getNodeByTitle as drupalGetNodeByTitle;
    createNode as drupalCreateNode;
  }

  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  /**
   * {@inheritdoc}
   *
   * I feel the power!
   */
  protected $runTestInSeparateProcess = FALSE;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A dummy content type with ID 'document'.
   *
   * @var \Drupal\node\NodeTypeInterface
   */
  protected $contentType;

  /**
   * A dummy node.
   *
   * @var \Drupal\node\Entity\Node;
   */
  protected $node;

  /**
   * A dummy file.
   *
   * @var \Drupal\file\Entity\File;
   */
  protected $file;


  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'filter',
    'field',
    'file',
    'file_entity',
    'country',
  ];

  protected function setUp() {
    parent::setUp();

    // Create the test user account.
    $this->account = $this->createUser(['uid' => 2]);

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->installConfig(['node', 'filter']);
    $this->installSchema('file', ['file_usage']);
    $this->installSchema('file_entity', ['file_metadata']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('file');
  }

  /**
   * Tests file access by group access.
   */
  public function testFileAccess() {
    $anonymous = User::create(array(
      'name' => '',
      'uid' => 0,
    ));

    $this->generateEntity();

    $access = country_file_access($this->file, 'view',  $anonymous);
    $this->assertEquals($access->isNeutral(), TRUE, 'File access neutral.');

    $access = country_file_access($this->file, 'download',  $anonymous);
    $this->assertEquals($access->isNeutral(), TRUE, 'File download access is neutral.');
  }


  /**
   * Create File entity.
   */
  public function generateEntity() {
    // Create a node type.
    $this->contentType = $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
    ]);

    $file_name = $this->randomMachineName() . '.txt';

    $this->file = File::create([
      'uri' => "public://$file_name",
    ]);
    $this->file->save();

    // Attach a file field to the node type.
    $file_field_storage = FieldStorageConfig::create([
      'type' => 'file',
      'entity_type' => 'node',
      'field_name' => 'field_file',
    ]);
    $file_field_storage->save();

    $file_field_instance = FieldConfig::create([
      'field_storage' => $file_field_storage,
      'entity_type' => 'node',
      'bundle' => $this->contentType->id(),
    ]);
    $file_field_instance->save();

    // Create some node.
    $this->node = $this->drupalCreateNode([
      'title' => 'A node with a file',
      'field_file' => [
        'target_id' => $this->file->id(),
        'display' => 0,
        'description' => 'An attached file',
      ]
    ]);
  }

}
