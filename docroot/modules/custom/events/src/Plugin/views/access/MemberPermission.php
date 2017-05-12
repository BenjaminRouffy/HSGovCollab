<?php

namespace Drupal\member\Plugin\views\access;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\member\Access\MemberPermissionHandlerInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Access plugin that provides member permission-based access control.
 *
 * @inmember views_access_plugins
 *
 * @ViewsAccess(
 *   id = "member_permission",
 *   title = @Translation("Member permission"),
 *   help = @Translation("Access will be granted to users with the specified member permission string.")
 * )
 */
class MemberPermission extends AccessPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  protected $usesOptions = TRUE;

  /**
   * The member permission handler.
   *
   * @var \Drupal\member\Access\MemberPermissionHandlerInterface
   */
  protected $permissionHandler;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The member entity from the route.
   *
   * @var \Drupal\member\Entity\MemberInterface
   */
  protected $member;

  /**
   * Constructs a Permission object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\member\Access\MemberPermissionHandlerInterface $permission_handler
   *   The member permission handler.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Plugin\Context\ContextProviderInterface $context_provider
   *   The member route context.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MemberPermissionHandlerInterface $permission_handler, ModuleHandlerInterface $module_handler, ContextProviderInterface $context_provider) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->permissionHandler = $permission_handler;
    $this->moduleHandler = $module_handler;

    /** @var \Drupal\Core\Plugin\Context\ContextInterface[] $contexts */
    $contexts = $context_provider->getRuntimeContexts(['member']);
    $this->member = $contexts['member']->getContextValue();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('member.permissions'),
      $container->get('module_handler'),
      $container->get('member.member_route_context')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    if (!empty($this->member)) {
      return $this->member->hasPermission($this->options['member_permission'], $account);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_member_permission', $this->options['member_permission']);

    // Upcast any %member path key the user may have configured so the
    // '_member_permission' access check will receive a properly loaded member.
    $route->setOption('parameters', ['member' => ['type' => 'entity:member']]);
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    $permissions = $this->permissionHandler->getPermissions(TRUE);
    if (isset($permissions[$this->options['member_permission']])) {
      return $permissions[$this->options['member_permission']]['title'];
    }

    return $this->t($this->options['member_permission']);
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['member_permission'] = array('default' => 'view member');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Get list of permissions
    $permissions = [];
    foreach ($this->permissionHandler->getPermissions(TRUE) as $permission_name => $permission) {
      $display_name = $this->moduleHandler->getName($permission['provider']);
      $permissions[$display_name][$permission_name] = strip_tags($permission['title']);
    }

    $form['member_permission'] = array(
      '#type' => 'select',
      '#options' => $permissions,
      '#title' => $this->t('Member permission'),
      '#default_value' => $this->options['member_permission'],
      '#description' => $this->t('Only users with the selected member permission will be able to access this display.<br /><strong>Warning:</strong> This will only work if there is a {member} parameter in the route. If not, it will always deny access.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['member_membership.roles.permissions'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return [];
  }

}
