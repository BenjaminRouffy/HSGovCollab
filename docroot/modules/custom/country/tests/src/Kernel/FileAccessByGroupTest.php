<?php

namespace Drupal\Tests\country\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\file_entity\Entity\FileType;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\group\Entity\Storage\GroupContentTypeStorageInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\user\Entity\User;

/**
 * Tests next previous functionality.
 *
 * @group Entity
 */
class FileAccessByGroupTest extends EntityKernelTestBase {

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
    'group',
    'group_test_config',
    'gnode',
    'node',
    'filter',
    'field',
    'file',
    'file_entity',
    'paragraphs',
    'entity_reference_revisions',
    'country',
  ];

  protected function setUp() {
    parent::setUp();

    // Create the test user account.
    $this->account = $this->createUser(['uid' => 2]);

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->installConfig(['group', 'node', 'group_test_config', 'filter']);
    $this->installSchema('file', ['file_usage']);
    $this->installSchema('file_entity', ['file_metadata']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('file');
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group_content');
    $this->installEntitySchema('group_content_type');
    $this->installEntitySchema('paragraph');
    \Drupal::moduleHandler()->loadInclude('paragraphs', 'install');
  }

  /**
   * Tests file access by group access.
   */
  public function testFileAccessInGroup() {
    $anonymous = User::create(array(
      'name' => '',
      'uid' => 0,
    ));

    $this->attachNodeTypeToGroup();
    $this->generateEntity();

    $access = country_file_access($this->file, 'view',  $anonymous);
    $this->assertEquals($access->isForbidden(), FALSE, 'File view is allowed.');

    $access = country_file_access($this->file, 'download',  $anonymous);
    $this->assertEquals($access->isForbidden(), FALSE, 'File download is allowed.');

    /* @var Group $group */
    $group = $this->entityTypeManager->getStorage('group')->create([
      'type' => 'default',
      'uid' => $this->account->id(),
      'label' => $this->randomMachineName(),
    ]);
    $group->save();

    // Attach node to group.
    $group->addContent($this->node, 'group_node:' . $this->contentType->id());

    $access = country_file_access($this->file, 'view',  $anonymous);
    $this->assertEquals($access->isForbidden(), TRUE, 'File view is blocked.');

    $access = country_file_access($this->file, 'download',  $anonymous);
    $this->assertEquals($access->isForbidden(), TRUE, 'File download is blocked.');
  }

  /**
   * Install group plugin.
   */
  public function attachNodeTypeToGroup() {
    // Create a node type.
    $this->contentType = $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
    ]);

    /* @var GroupTypeInterface $group_type */
    $group_type = $this->entityTypeManager->getStorage('group_type')->load('default');

    // Install some node types on some group types.
    /** @var GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    $storage->createFromPlugin($group_type, 'group_node:' . $this->contentType->id())->save();
  }

  /**
   * Create File entity.
   */
  public function generateEntity() {
    $file_type = FileType::create(['id' => 'document']);
    $file_type->save();

    $file_name = $this->randomMachineName() . '.txt';
    file_put_contents("public://$file_name", $this->randomString());

    $this->file = File::create([
      'type' => $file_type->id(),
      'uri' => "public://$file_name",
    ]);
    $this->file->save();

    $paragraph_type = ParagraphsType::create(array(
      'label' => 'File',
      'id' => 'file',
    ));
    $paragraph_type->save();

    // Attach a file field to the node type.
    $file_field_storage = FieldStorageConfig::create([
      'type' => 'file',
      'entity_type' => 'paragraph',
      'field_name' => 'field_file',
    ]);
    $file_field_storage->save();

    $file_field_instance = FieldConfig::create([
      'field_storage' => $file_field_storage,
      'entity_type' => 'paragraph',
      'bundle' => $paragraph_type->id(),
    ]);

    $file_field_instance->save();

    $paragraph1 = Paragraph::create([
      'title' => 'Paragraph',
      'type' => 'file',
      'field_file' => [
        'target_id' => $this->file->id(),
        'display' => 0,
        'description' => 'An attached file',
      ]
    ]);

    // Add a paragraph field to the article.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'node_paragraph_field',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'cardinality' => '-1',
      'settings' => array(
        'target_type' => 'paragraph'
      ),
    ));

    $field_storage->save();
    $field = FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => $this->contentType->id(),
    ));
    $field->save();

    // Create some node.
    $this->node = $this->drupalCreateNode([
      'title' => 'A node with a file',
      'node_paragraph_field' => [$paragraph1],
    ]);
  }

}
