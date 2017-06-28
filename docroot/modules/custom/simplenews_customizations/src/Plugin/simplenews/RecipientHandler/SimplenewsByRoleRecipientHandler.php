<?php

namespace Drupal\simplenews_customizations\Plugin\simplenews\RecipientHandler;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simplenews\RecipientHandler\RecipientHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\simplenews_customizations\RecipientHandlerBaseTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group_following\GroupFollowingManager;

/**
 * @RecipientHandler(
 *  id = "simplenews_by_role",
 *  title = @Translation("Simplenews by role."),
 *  description = @Translation("Simplenews by role."),
 *  types = {
 *    "by_roles"
 *  }
 * )
 */
class SimplenewsByRoleRecipientHandler extends RecipientHandlerExtraBase implements RecipientHandlerInterface, ContainerFactoryPluginInterface {

  use RecipientHandlerBaseTrait;

  /**
   * Drupal\group_following\GroupFollowingManager definition.
   *
   * @var \Drupal\group_following\GroupFollowingManager
   */
  protected $groupFollowingManager;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GroupFollowingManager $group_following_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->groupFollowingManager = $group_following_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('group_following.manager')
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
    if (!empty($settings['type']) && $settings['type'] != 'authenticated') {
      $select->join('user__roles', 'ur', db_and()
        ->where('ud.uid = ur.entity_id')
      );
      $select->condition('ur.roles_target_id', $settings['type']);
    }

    return $select;
  }

  /**
   *
   */
  public function settingsForm($element, $settings, $form_state) {

    $roles = user_roles(TRUE);
    $element['type'] = [
      '#title' => t('Type'),
      '#type' => 'select',
      '#default_value' => $settings['type'],
      '#options' => array_map(function ($role) {
        /** @var \Drupal\user\Entity\Role $role */
        return Xss::filter($role->label());
      }, $roles),
      '#ajax' => [
        'callback' => [$this, 'ajaxUpdateRecipientHandlerSettings'],
        'wrapper' => 'recipient-handler-count',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];

    // Add some text to describe the send situation.
    $subscriber_count = $this->count($this->settingsFormSubmit([], $form_state));
    $element['count'] = [
      '#type' => 'item',
      '#markup' => t('Send newsletter issue to @count subscribers.', ['@count' => $subscriber_count]),
      '#parents' => ['recipient_handler_settings'],
      '#prefix' => '<div id="recipient-handler-count">',
      '#suffix' => '</div>',
    ];
    return $element;
  }

  /**
   *
   */
  public static function settingsFormSubmit($settings, FormStateInterface $form_state) {
    $values = [];
    foreach (['type'] as $item) {
      if ($form_state->getValue($item)) {
        $values[$item] = $form_state->getValue($item);
      }
    }
    return $values;
  }

  /**
   * @TODO remane
   */
  public function ajaxUpdateRecipientHandlerSettings($form, FormStateInterface $form_state) {
    return empty($form['send']['recipient_handler_settings']['count']) ? [] : $form['send']['recipient_handler_settings']['count'];

  }

}
