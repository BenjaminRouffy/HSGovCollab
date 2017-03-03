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

    if (\Drupal::currentUser()->isAuthenticated()) {
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

      $entities = [
        'project' => $this->t('Projects'),
        'news_and_event' => $this->t('News&Events'),
        'document' => $this->t('Documents'),
        'contact' => $this->t('Contacts'),
        'faq' => $this->t('FAQ'),
      ];

      foreach ($entities as $index => $title) {
        $row = [];

        switch($index) {
          case 'news_and_event':
            $row = views_get_view_result('news_and_events_group', 'news_and_events_by_group');
            break;

          case 'project':
            $row = views_get_view_result('list_of_projects', 'block_1');
            break;

          case 'document':
            $row = views_get_view_result('news_and_events_group', 'documents_by_group');
            break;

          case 'contact':
          case 'faq':

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