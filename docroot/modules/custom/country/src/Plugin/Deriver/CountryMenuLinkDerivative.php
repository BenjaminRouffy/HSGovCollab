<?php

namespace Drupal\country\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CountryMenuLinkDerivative extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $related_entity = [
      'projects' => $this->t('Projects'),
      'news_and_events' => $this->t('News&Events'),
      'documents' => $this->t('Documents'),
      'contacts' => $this->t('Contacts'),
      'faq' => $this->t('FAQ'),
    ];
    $links = [];

    foreach ($related_entity as $type => $title) {
      $links["country_$type"] = [
        'title' => $title,
        'menu_name' => 'country-menu',
        'route_name' => "fake.$type",
      ] + $base_plugin_definition;
    }


    return $links;
  }

}
