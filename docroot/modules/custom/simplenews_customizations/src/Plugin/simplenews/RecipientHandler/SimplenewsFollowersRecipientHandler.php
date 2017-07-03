<?php

namespace Drupal\simplenews_customizations\Plugin\simplenews\RecipientHandler;

use Drupal\group\GroupMembershipLoader;
use Drupal\simplenews\RecipientHandler\RecipientHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\simplenews_customizations\RecipientHandlerBaseTrait;
use Drupal\simplenews_customizations\RecipientHandlerCountryTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group_following\GroupFollowingManager;

/**
 * @RecipientHandler(
 *  id = "simplenews_followers",
 *  title = @Translation("Simplenews by group."),
 *  description = @Translation("Simplenews by group."),
 *  types = {
 *    "followers"
 *  }
 * )
 */
class SimplenewsFollowersRecipientHandler extends RecipientHandlerExtraBase implements RecipientHandlerInterface, ContainerFactoryPluginInterface {

  use RecipientHandlerBaseTrait, RecipientHandlerCountryTrait;

  /**
   * Drupal\group_following\GroupFollowingManager definition.
   *
   * @var \Drupal\group_following\GroupFollowingManager
   */
  protected $groupFollowingManager;

  /**
   * @var \Drupal\group\GroupMembershipLoader
   */
  protected $groupMembershipLoader;

  /**
   * Constructs a new SimplenewsByTypeRecipientHandler object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GroupFollowingManager $group_following_manager, GroupMembershipLoader $group_membership_loader) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->groupFollowingManager = $group_following_manager;
    $this->groupMembershipLoader = $group_membership_loader;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('group_following.manager'),
      $container->get('group.membership_loader')
    );
  }

  /**
   *
   */
  public function buildRecipientQuery($settings = NULL) {
    $select = parent::buildRecipientQuery('default');

    $select->join('users_field_data', 'ud', db_and()
      ->where('s.mail = ud.mail')
    );

    if (is_null($settings) || empty($settings)) {
      $settings = $this->configuration;
    }
    if (isset($settings['type']) && !empty($settings['type'])) {
      /** @var \Drupal\group\Entity\Group $group */
      $group = entity_load('group', $settings['type']);
      /** @var \Drupal\group\GroupMembership[] $members */
      $members = $group->getMembers();

      $following_join = $this->groupFollowingManager->getStorage()->buildJoinQuery();
      $select->leftJoin($following_join, 'f', db_and()
        ->where('ud.uid=f.uid')
      );
      $or = db_or()
        ->condition('f.gid', $group->id());
      $uids = [];
      if (!empty($members)) {
        foreach ($members as $member) {
          $uids[] = $member->getUser()->id();
        }
        $or->condition('ud.uid', $uids, 'IN');
      }

      $select->condition($or);
    }

    return $select;
  }

}
