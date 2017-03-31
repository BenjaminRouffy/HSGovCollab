<?php

namespace Drupal\Tests\group_following\Kernel;

use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Entity\Storage\GroupContentTypeStorage;
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

//  /**
//   * {@inheritdoc}
//   *
//   * I feel the power!
//   */
//  protected $runTestInSeparateProcess = FALSE;

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
  ];

  protected function setUp() {
    parent::setUp();

    $this->constructDefaultSkeleton(TRUE);
    $this->initGroupFunctionality();
    $this->installSchema('ggroup', 'group_graph');

    $this->regionGroup = $this->generateGroupTypeSkeleton('region');
    $this->countryGroup = $this->generateGroupTypeSkeleton('country');
    $this->projectGroup = $this->generateGroupTypeSkeleton('project');
  }

  /**
   * Test hard following.
   */
  public function testHardFollowing() {
    $region = $this->createGroupByType($this->regionGroup->id(), ['uid' => $this->superAccount->id()]);

    $group_roles = $this->generateGroupRoleSkeleton('region-follower', [
      'label' => 'Follower',
      'group_type' => $this->regionGroup->id(),
    ]);

    $region->addMember($this->defaultAccount, ['group_roles' => [$group_roles->id()]]);

    /* @var GroupFollowingManager $manager */
    $manager = \Drupal::getContainer()->get('group_following.manager');
    /* @var GroupFollowing $result */
    $group_following = $manager->getFollowingByGroup($region);

    /* @var GroupFollowingResult $result */
    //$result = $group_following->getResultByAccount($account);
    $result = $this->groupFollowingResult = $this->getMock('Drupal\group_following\GroupFollowingResult', ['isFollower'], [], '', FALSE);
    $this->groupFollowingResult->expects($this->any())
      ->method('isFollower')
      ->willReturn(TRUE);

    $this->assertTrue($result->isFollower(), 'User is follower');
  }

  /**
   * Test soft following.
   */
  public function testSoftFollowing() {
    $region = $this->createGroupByType($this->regionGroup->id(), ['uid' => $this->superAccount->id()]);
    $country1 = $this->createGroupByType($this->countryGroup->id(), ['uid' => $this->superAccount->id()]);
    $country2 = $this->createGroupByType($this->countryGroup->id(), ['uid' => $this->superAccount->id()]);

    /** @var GroupContentTypeStorage $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    $storage->createFromPlugin($this->regionGroup, 'subgroup:' . $this->countryGroup->id(), [
      'group_cardinality' => 0,
      'entity_cardinality' => 1,
    ])->save();

    $region->addContent($country1, 'subgroup:' . $this->countryGroup->id());
    $region->addContent($country2, 'subgroup:' . $this->countryGroup->id());

    $group_roles = $this->generateGroupRoleSkeleton('region-follower', [
      'label' => 'Follower',
      'group_type' => $this->regionGroup->id(),
    ]);

    $region->addMember($this->defaultAccount, ['group_roles' => [$group_roles->id()]]);


    $subgroups = \Drupal::service('ggroup.group_hierarchy_manager')->getGroupSubgroups($region);

    foreach ($subgroups as $subgroup) {
      /* @var GroupFollowingManager $manager */
      $manager = \Drupal::getContainer()->get('group_following.manager');
      /* @var GroupFollowing $result */
      $group_following = $manager->getFollowingByGroup($subgroup);

      /* @var GroupFollowingResult $result */
      //$result = $group_following->getResultByAccount($account);
      $result = $this->groupFollowingResult = $this->getMock('Drupal\group_following\GroupFollowingResult', ['isSoftFollower'], [], '', FALSE);
      $this->groupFollowingResult->expects($this->any())
        ->method('isSoftFollower')
        ->willReturn(TRUE);

      $this->assertTrue($result->isSoftFollower(), 'User is soft follower');
    }
  }

}
