<?php

namespace Drupal\Tests\group_content_field\Kernel;

use Drupal\group\Entity\Group;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\prev_next_access\Plugin\Block\GroupJoinBlock;
use Drupal\Tests\phpunit_skeleton\Kernel\TestsSkeletonTrait;

/**
 * Tests next previous functionality.
 *
 * @group Entity
 */
class GroupContentItemTest extends EntityKernelTestBase {
  use TestsSkeletonTrait;
  /**
   * {@inheritdoc}
   *
   * I feel the power!
   */
  protected $runTestInSeparateProcess = FALSE;

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
    'language',
  ];
  /**
   * @var \Drupal\group_content_field\Plugin\Field\FieldType\GroupContentItem
   */
  protected $groupContentItem;

  protected function setUp() {
    parent::setUp();
  }


}
