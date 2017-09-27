<?php

namespace Drupal\events\Custom\views\display;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\auto_login_url\AutoLoginUrlCreate;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\ViewExecutable;
use Drupal\views_data_export\Plugin\views\display\DataExport as DefaultDataExport;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class DataExport extends DefaultDataExport implements ContainerFactoryPluginInterface {

  /**
   * AutoLoginUrlCreate class.
   *
   * @var \Drupal\auto_login_url\AutoLoginUrlCreate
   */
  private $autoLoginUrl;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('state'),
      $container->get('renderer'),
      $container->getParameter('authentication_providers'),
      $container->get('auto_login_url.create')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteProviderInterface $route_provider, StateInterface $state, RendererInterface $renderer, $authentication_providers, AutoLoginUrlCreate $auto_login_url_create) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_provider, $state, $renderer, $authentication_providers);
    $this->autoLoginUrl = $auto_login_url_create;
  }

  /**
   * {@inheritdoc}
   */
  public function attachTo(ViewExecutable $clone, $display_id, array &$build) {
    global $base_url;
    $displays = $this->getOption('displays');
    if (empty($displays[$display_id])) {
      return;
    }

    // Defer to the feed style; it may put in meta information, and/or
    // attach a feed icon.
    $clone->setArguments($this->view->args);
    $clone->setDisplay($this->display['id']);
    $clone->buildTitle();

    /** @var \Drupal\views_data_export\Plugin\views\style\DataExport $plugin */
    if ($plugin = $clone->display_handler->getPlugin('style')) {
      /** @var \Drupal\Core\Url $url */
      $url = $clone->getUrl();
      /** @var \Drupal\Core\Session\AccountInterface $user */
      $user = \Drupal::currentUser()->getAccount();
      // @TODO I'm not sure that calling create is absolutely correct approach.
      if (($auth = $this->getOption('auth')) && in_array('auto_login_url', $auth)) {
        $hash = @end(explode('/', $this->autoLoginUrl->create($user->id(), '<front>', FALSE)));
        $url->setOption('query', [
          'provider_user' => $user->id(),
          'provider_access' => $hash,
        ]);
      }
      if (isset($plugin->getFormats()['ical'])) {
        $url->setOption('base_url', preg_replace('~^(\w{2,5})\:~is', 'webcal:', $base_url));
      }
      $plugin->attachTo($build, $display_id, $url, $clone->getTitle());
      foreach ($clone->feedIcons as $feed_icon) {
        $this->view->feedIcons[] = $feed_icon;
      }
    }

    // Clean up.
    $clone->destroy();
    unset($clone);
  }

}
