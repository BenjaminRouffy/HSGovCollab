<?php

namespace Drupal\Tests\group_following\Kernel;

use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group_following\GroupFollowing;
use Drupal\group_following\GroupFollowingManager;
use Drupal\group_following\GroupFollowingResult;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\phpunit_skeleton\Kernel\TestsSkeletonTrait;


/**
 * Tests next previous functionality.
 *
 * @group Entity
 */
class SimpleGroupFollowingTest extends EntityKernelTestBase {
  use TestsSkeletonTrait;

  /**
   * {@inheritdoc}
   *
   * I feel the power!
   */
  // protected $runTestInSeparateProcess = FALSE;

  /**
   * Region group type.
   *
   * @var GroupTypeInterface
   */
  protected $regionGroup;

  /**
   * Country group type.
   *
   * @var GroupTypeInterface
   */
  protected $countryGroup;

  /**
   * Region group type.
   *
   * @var GroupTypeInterface
   */
  protected $projectGroup;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'group',
    'group_test_config',
    'ggroup',
    'phpunit_skeleton',
    'group_following',
    'field_permissions',
    'group_following_test',
  ];

  protected function setUp() {
    parent::setUp();

    $this->constructDefaultSkeleton(TRUE);
    $this->initGroupFunctionality();
    $this->installSchema('ggroup', 'group_graph');
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_type');
    $this->installEntitySchema('group_content');
    $this->installEntitySchema('group_content_type');
    $this->installConfig(['group_following_test']);

    $this->groupType = \Drupal::entityTypeManager()->getStorage('group_type');
    $this->regionGroup = $this->groupType->load('region');
    $this->countryGroup = $this->groupType->load('country');
    $this->projectGroup = $this->groupType->load('project');
  }

  /**
   * Test hard following.
   */
  public function testHardFollowing() {
    $region = $this->createGroupByType($this->regionGroup->id(), ['uid' => $this->superAccount->id()]);

    $region->addMember($this->defaultAccount, [
      'field_follower' => [
        'value' => 1,
      ],
    ]);

    /* @var GroupFollowingManager $manager */
    $manager = \Drupal::getContainer()->get('group_following.manager');
    /* @var GroupFollowing $result */
    $group_following = $manager->getFollowingByGroup($region);

    /* @var GroupFollowingResult $result */
    $result = $group_following->getResultByAccount($this->defaultAccount);
    /*
     * Mock example.
     *
     * @example
     * $result = $this->groupFollowingResult = $this->getMock('Drupal\group_following\GroupFollowingResult', ['isFollower'], [], '', FALSE);
     * $this->groupFollowingResult->expects($this->any())
     *  ->method('isFollower')
     *  ->willReturn(TRUE);
     */

    $this->assertTrue($result->isFollower(), 'User is follower');
  }

  /**
   * Test soft following.
   */
  public function testSoftFollowing() {
    $region = $this->createGroupByType($this->regionGroup->id(), ['uid' => $this->superAccount->id()]);
    $country1 = $this->createGroupByType($this->countryGroup->id(), ['uid' => $this->superAccount->id()]);
    $country2 = $this->createGroupByType($this->countryGroup->id(), ['uid' => $this->superAccount->id()]);

    $region->addContent($country1, 'subgroup:' . $this->countryGroup->id());
    $region->addContent($country2, 'subgroup:' . $this->countryGroup->id());

    $region->addMember($this->defaultAccount, [
      'field_follower' => [
        'value' => 1,
      ],
    ]);

    $subgroups = \Drupal::service('ggroup.group_hierarchy_manager')
      ->getGroupSubgroups($region);

    foreach ($subgroups as $subgroup) {
      /* @var GroupFollowingManager $manager */
      $manager = \Drupal::getContainer()->get('group_following.manager');
      /* @var GroupFollowing $result */
      $group_following = $manager->getFollowingByGroup($subgroup);

      /* @var GroupFollowingResult $result */
      $result = $group_following->getResultByAccount($this->defaultAccount);
      /*
       * Mock example.
       *
       * @example
       * $result = $this->groupFollowingResult = $this->getMock('Drupal\group_following\GroupFollowingResult', ['isSoftFollower'], [], '', FALSE);
       * $this->groupFollowingResult->expects($this->any())
       *  ->method('isSoftFollower')
       *  ->willReturn(TRUE);
       */

      $this->assertTrue($result->isSoftFollower(), 'User is soft follower');
    }
  }

}
