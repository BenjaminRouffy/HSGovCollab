<?php

namespace Drupal\Tests\prev_next_access\Kernel;

use Drupal\group\Entity\Group;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\prev_next_access\Plugin\Block\NextPreviousAccessBlock;
use Drupal\Tests\phpunit_skeleton\Kernel\TestsSkeletonTrait;

/**
 * Tests next previous functionality.
 *
 * @group Entity
 */
class NextPreviousAccessBlockTest extends EntityKernelTestBase {
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
   * @var NextPreviousAccessBlock
   */
  private $nextPrevBlock;

  protected function setUp() {
    parent::setUp();

    $this->constructDefaultSkeleton();
    $this->initGroupFunctionality();
    $this->nextPrevBlock = $this->getMock('\Drupal\prev_next_access\Plugin\Block\NextPreviousAccessBlock', ['getContextValue'], [], '', FALSE);

    $this->nextPrevBlock->expects($this->any())
      ->method('getContextValue')
      ->will($this->returnValue([]));

  }

  /**
   * Tests generate next previous.
   */
  public function testGenerateNextPrevious() {
    $container = \Drupal::getContainer();
    $container->get('current_user')->setAccount($this->superAccount);
    $content_type = $this->generateNodeTypeSkeleton();

    // Create a test node.
    $english = Node::create(array(
      'type' => $content_type->id(),
      'title' => $this->randomMachineName(),
      'language' => 'en',
    ));
    $english->save();

    $link = $this->nextPrevBlock->generateNextPrevious($english);

    // Zero result.
    $this->assertEquals([], $link);

    // Create a test node.
    /* @var Node $english2 */
    $english2 = Node::create(array(
      'type' => $content_type->id(),
      'title' => $this->randomMachineName(),
      'language' => 'en',
    ));
    $english2->save();

    $this->generateNextPrevHelper($english2, $english, 'prev', 'Previous');
    $this->generateNextPrevHelper($english, $english2);

    // Create a test node.
    /* @var Node $english3 */
    $english3 = Node::create(array(
      'type' => $content_type->id(),
      'title' => $this->randomMachineName(),
      'language' => 'en',
    ));
    $english3->save();

    $this->generateNextPrevHelper($english2, $english, 'prev', 'Previous');
    $this->generateNextPrevHelper($english2, $english3);

    $group_type = $this->getDefaultGroupType();
    /* @var Group $group */
    $group = $this->createGroupByType($group_type->id(), ['uid' => $this->superAccount->id()]);

    $this->nextPrevBlock = $this->getMock('\Drupal\prev_next_access\Plugin\Block\NextPreviousAccessBlock', ['getContextValue'], [], '', FALSE);

    $this->nextPrevBlock->expects($this->any())
      ->method('getContextValue')
      ->will($this->returnValue([$group]));

    // Attach nodes to group.
    $this->attachContentTypeToGroup($group_type, $content_type->id());
    $group->addContent($english, 'group_node:' . $content_type->id());
    $group->addContent($english3, 'group_node:' . $content_type->id());

    $this->generateNextPrevHelper($english3, $english, 'prev', 'Previous');
    $this->generateNextPrevHelper($english, $english3);

  }

  /**
   * Helper.
   */
  function generateNextPrevHelper($node, $target_node, $direction = 'next', $direction_pretty = 'Next') {
    $link = $this->nextPrevBlock->generateNextPrevious($node, $direction);

    $this->assertEquals($target_node->id(), $link['#url']->getRouteParameters()['node']);
    $this->assertEquals("<span>$direction_pretty story</span><p>{$target_node->getTitle()}</p>", (string) $link['#title']);

  }

}
