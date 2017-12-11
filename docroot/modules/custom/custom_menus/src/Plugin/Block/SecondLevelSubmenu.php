<?php

namespace Drupal\custom_menus\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a 'SecondLevelSubmenu' block.
 *
 * @Block(
 *   id = "second_level_submenu",
 *   admin_label = @Translation("Second level submenu"),
 *   category = @Translation("Menus"),
 * )
 */
class SecondLevelSubmenu extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * The active menu trail service.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $menuActiveTrail;

  /**
   * Constructs a new SecondLevelSubmenu.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuLinkTreeInterface $menu_tree, MenuActiveTrailInterface $menu_active_trail) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuTree = $menu_tree;
    $this->menuActiveTrail = $menu_active_trail;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree'),
      $container->get('menu.active_trail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
         'menu_name' => $this->t(''),
        ] + parent::defaultConfiguration();

 }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['menu_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Menu name'),
      '#description' => $this->t(''),
      '#default_value' => $this->configuration['menu_name'],
      '#maxlength' => 64,
      '#size' => 64,
      '#weight' => '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['menu_name'] = $form_state->getValue('menu_name');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $menu_name = $this->configuration['menu_name'];
    if (!$menu_name || !($menus = Menu::loadMultiple([$menu_name]))) {
      $build['no_menu']['#markup'] = '<p>Menu missing</p>';
    }
    else {
      $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
      $parameters->setMinDepth(1);
      $parameters->setMaxDepth(2);

      $tree = $this->menuTree->load($menu_name, $parameters);
      $active1stlevel = NULL;
      // Iterate over the first level items and get the second level subtree
      // of the first level item that is currently in active trail.
      foreach ($tree as $key => $element) {
        if ($element->inActiveTrail && $element->depth == 1) {
          $tree = $element->subtree;
          break;
        }
      }
      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];
      $tree = $this->menuTree->transform($tree, $manipulators);
      $build['the_menu_tree_build'] = $this->menuTree->build($tree);
    }

    return $build;
  }

}
