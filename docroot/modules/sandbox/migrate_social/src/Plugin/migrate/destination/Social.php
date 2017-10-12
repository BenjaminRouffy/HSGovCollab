<?php

namespace Drupal\migrate_social\Plugin\migrate\destination;

use Drupal\ban\BanIpManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Send data to social.
 *
 * @MigrateDestination(
 *   id = "social_destination"
 * )
 */
class Social extends DestinationBase {

  /**
   * The social network plugin.
   *
   * @var \Drupal\migrate_plus\DataParserPluginInterface
   */
  protected $socialNetworkPlugin;

  /**
   * Returns the initialized data parser plugin.
   *
   * @return \Drupal\migrate_plus\DataParserPluginInterface
   *   The data parser plugin.
   */
  public function getSocialNetworkPlugin() {
    if (!isset($this->socialNetworkPlugin)) {
      $this->socialNetworkPlugin = \Drupal::service('plugin.manager.migrate_social.social_network')->createInstance($this->configuration['social_network_plugin'], $this->configuration);
    }
    return $this->socialNetworkPlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
       'id' => [
         'type' => 'string',
         'size' => '64',
       ],
     ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    return [
      'message' => $this->t('Body of post.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $social_plugin = $this->getSocialNetworkPlugin();
    return $social_plugin->import($row, $old_destination_id_values);
  }

}
