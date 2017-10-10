<?php

namespace Drupal\country\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupInterface;

/**
 * Provides a block with operations the user can perform on a group.
 *
 * @Block(
 *   id = "country_menu_block",
 *   admin_label = @Translation("Country menu block")
 * )
 */
class CountryMenuBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['#cache'] = ['contexts' => $this->getCacheContexts()];

    /* @var GroupInterface $group */
    if (($group = \Drupal::routeMatch()->getParameter('group')) && $group->id()) {
      $links = [];

      $links += $this->generateLinks($group);

      if (!empty($links) && count($links) > 1) {
        $build['links'] = $links;
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

  /**
   * Generate links by group
   *
   * @param GroupInterface $group
   *   The group to generate links.
   *
   * @return array
   *   Return links.
   */
  public function generateLinks(GroupInterface $group) {
    // @todo Create test.
    $links = [];
    /** @var \Drupal\Core\Session\AccountInterface $account */
    $account = \Drupal::currentUser();
    if ($account->isAuthenticated()) {
      $current_path = \Drupal::request()->getRequestUri();
      $group_url = Url::fromRoute("entity.group.canonical", [
        'group' => $group->id(),
      ]);

      $links[] = [
        'link' => Link::fromTextAndUrl(
          $group->label(),
          $group_url
        ),
        'active' => $group_url->toString() === $current_path,
      ];

      // @TODO: Block cache has to be invalidated on node save.
      $label = $this->t('Countries');
      if ($group->hasField('field_label') && !empty($group->get('field_label')->value)) {
        $label = $group->get('field_label')->value;
      }

      $entities = [
        // Country menu item could be overridden by label field.
        'country' => $label,
        'project' => $this->t('Collaborations'),
        'news_and_event' => $this->t('News&Events'),
        'document' => $this->t('Documents'),
        'contact' => $this->t('Contacts'),
        'calendar' => $this->t('Calendar'),
        'faq' => $this->t('FAQ'),
      ];

      foreach ($entities as $index => $title) {
        $row = [];

        switch($index) {
          case 'country':
            // It is possible to have country link at Governance area, and in
            // this case we need to get the result from other view.
            if ($group->bundle() == 'governance_area') {
              $row = views_get_view_result('search_for_a_country_or_region', 'block_4');
            }
            else {
              $row = views_get_view_result('search_for_a_country_or_region', 'block_2');
            }
            break;

          case 'news_and_event':
            if ($group->bundle() == 'region') {
              $row = views_get_view_result('news_and_events_group', 'region_news_events');
            }
            elseif ($group->bundle() == 'governance_area') {
              $row = views_get_view_result('news_and_events_group', 'ga_news_events');
            }
            else {
              $row = views_get_view_result('news_and_events_group', 'news_and_events_by_group');
            }
            break;

          case 'project':
            if ($group->bundle() == 'region') {
              $row = views_get_view_result('list_of_projects', 'block_2');
            }
            elseif ($group->bundle() == 'governance_area') {
              $row = views_get_view_result('list_of_projects', 'block_4');
            }
            else {
              $row = views_get_view_result('list_of_projects', 'block_1');
            }
            break;

          case 'document':
            $row = views_get_view_result('news_and_events_group', 'documents_by_group');
            break;

          case 'contact':
            $row = ['not-null'];
            break;

          case 'calendar':
            // @TODO Dependencies injection.
            // List with groups that should have access to calendar page.
            $groups = array(
              'governance_area',
              'region',
              'country',
              'project',
            );
            $check_by_group = \Drupal::service('menu_item_visibility_by_group.check_by_group');
            if (in_array($group->getGroupType()->id(), $groups) && $check_by_group->check($account, $groups)) {
              $row = ['not-null'];
            }
            break;

          case 'faq':
            if ($group->hasField('field_faq')) {
              $row = $group->get('field_faq')->getValue();
            }

            break;

        }

        if (empty($row)) {
          continue;
        }

        $url = Url::fromRoute("group.$index", ['group' => $group->id()]);

        $links[] = [
          'link' => Link::fromTextAndUrl($title, $url),
          'active' => $url->toString() == $current_path,
        ];
      }
    }

    return $links;
  }

}
