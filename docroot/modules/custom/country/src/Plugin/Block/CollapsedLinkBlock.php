<?php

namespace Drupal\country\Plugin\Block;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\country\CountryCollapsibleService;

/**
 * Provides a 'CollapsedLinkBlock' block.
 *
 * @Block(
 *  id = "collapsed_link_block",
 *  admin_label = @Translation("Collapsed link block"),
 *  context = {
 *    "group" = @ContextDefinition("entity:group", required = TRUE)
 *  }
 * )
 */
class CollapsedLinkBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\country\CountryCollapsibleService definition.
   *
   * @var \Drupal\country\CountryCollapsibleService
   */
  protected $countryPreviewCollapsed;

  /**
   * Constructs a new CollapsedLinkBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\country\CountryCollapsibleService $country_preview_collapsed
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    CountryCollapsibleService $country_preview_collapsed
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->countryPreviewCollapsed = $country_preview_collapsed;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResultAllowed::allowedIf($account->isAuthenticated());
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('country.collapsible_display_mode')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = $this->getContextValue('group');

    $build = [];
    $build['colapsed_link_block'] = [
      '#prefix' => '<div class="group-link-collapsible-wrapper">',
      '#suffix' => '</div>',
      '#type' => 'link',
      '#title' => 'view details',
      '#url' => Url::fromRoute('country.collapsed_update', [
        'js' => 'nojs',
        'group' => $group->id(),
      ]),
      '#attributes' => [
        'class' => [
          'group-link-collapsible',
          'use-ajax',
          $this->countryPreviewCollapsed->isCollapsedText($group->id()),
        ],
        'data-group-collapsible' => $group->id(),
      ],
      '#attached' => [
        'library' => [
          'core/jquery.form',
          'core/drupal.ajax',
        ],
      ],
    ];
    return $build;
  }

}
