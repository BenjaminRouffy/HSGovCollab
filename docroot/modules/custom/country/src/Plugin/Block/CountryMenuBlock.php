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

    $build['#cache'] = [
      'contexts' => $this->getCacheContexts()
    ];

    /** @var \Drupal\group\Entity\GroupInterface $group */
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
        $query = [];
        $group_ids = \Drupal::database()->select('group_graph', 'group_graph')
          ->fields('group_graph', ['end_vertex'])
          ->condition('start_vertex', $group->id())
          ->condition('hops', 0)
          ->execute()
          ->fetchCol();

        switch($index) {
          case 'news_and_event':
            $group_ids[] = $group->id();

            foreach (['event', 'news'] as $type) {
              $query += \Drupal::database()->select('group_content_field_data', 'group_content_field_data')
                ->fields('group_content_field_data', ['id'])
                ->condition('gid', $group_ids, 'IN')
                ->condition('type', "%-group_node-$type", 'LIKE')
                ->execute()
                ->fetchCol();
            }
            break;

          case 'project':
            if ('project' == $group->bundle()) {
              continue 2;
            }

            $query = $group_ids;

            break;

          case 'document':
          case 'contact':
          case 'faq':
            $group_ids[] = $group->id();

            $query = \Drupal::database()->select('group_content_field_data', 'group_content_field_data')
              ->fields('group_content_field_data', ['id'])
              ->condition('gid', $group_ids, 'IN')
              ->condition('type', '%-group_node-' . $index, 'LIKE')
              ->execute()
              ->fetchCol();
            break;
        }

        if (empty($query)) {
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
