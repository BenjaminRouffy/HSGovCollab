<?php

namespace Drupal\Tests\prev_next_access\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\prev_next_access\Plugin\Block\NextPreviousAccessBlock;

/**
 * Tests next previous functionality.
 *
 * @group Entity
 */
class NextPreviousAccessBlockTest extends EntityKernelTestBase {
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
  public static $modules = ['node', 'language'];

  /**
   * @var NextPreviousAccessBlock
   */
  private $nextPrevBlock;

  protected function setUp() {
    parent::setUp();

    $this->nextPrevBlock = $this->getMock('\Drupal\prev_next_access\Plugin\Block\NextPreviousAccessBlock', ['getContextValue'], [], '', FALSE);

    $this->nextPrevBlock->expects($this->any())
      ->method('getContextValue')
      ->will($this->returnValue([]));

    // Create the node bundles required for testing.
    $type = NodeType::create(array(
      'type' => 'page',
      'name' => 'page',
    ));
    $type->save();

    // Enable two additional languages.
    ConfigurableLanguage::createFromLangcode('de')->save();
    ConfigurableLanguage::createFromLangcode('it')->save();

    $this->installSchema('node', 'node_access');
  }

  /**
   * Tests generate next previous.
   */
  public function testGenerateNextPrevious() {
    $user = $this->createUser();

    $container = \Drupal::getContainer();
    $container->get('current_user')->setAccount($user);

    // Create a test node.
    $english = Node::create(array(
      'type' => 'page',
      'title' => $this->randomMachineName(),
      'language' => 'en',
    ));
    $english->save();

    $link = $this->nextPrevBlock->generateNextPrevious($english);

    // Zero result.
    $this->assertEquals([], $link);

    // Create a test node.
    $english2 = Node::create(array(
      'type' => 'page',
      'title' => $this->randomMachineName(),
      'language' => 'en',
    ));
    $english2->save();

    $this->generateNextPrevHelper($english2, $english, 'prev', 'Previous');
    $this->generateNextPrevHelper($english, $english2);

    // Create a test node.
    $english3 = Node::create(array(
      'type' => 'page',
      'title' => $this->randomMachineName(),
      'language' => 'en',
    ));
    $english3->save();

    $this->generateNextPrevHelper($english2, $english, 'prev', 'Previous');
    $this->generateNextPrevHelper($english2, $english3);

    $german = $english->addTranslation('de');
    $german->title = $this->randomString();
    $german->save();

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
