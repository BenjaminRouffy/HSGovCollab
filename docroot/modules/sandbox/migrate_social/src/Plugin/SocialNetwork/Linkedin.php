<?php

namespace Drupal\migrate_social\Plugin\SocialNetwork;

use Drupal\Core\Database\Database;
use Drupal\group\Entity\GroupContentType;
use Drupal\migrate\Row;
use Drupal\migrate_social\SocialNetworkBase;
use Drupal\views\Views;

/**
 * Provides a twitter migrate plugin
 *
 * @SocialNetwork(
 *   id = "linkedin",
 *   description = @Translation("Linkedin migrate plugin.")
 * )
 */
class Linkedin extends SocialNetworkBase {

  var $companyId = 0;

  /**
   * @inheritdoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->companyId = $this->instance->setResponseDataType('array')->get("v1/companies?format=json&is-company-admin=true")['values'][0]['id'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSocialRows() {
    if (!empty($this->configuration['source_type'])) {
      switch ($this->configuration['source_type']) {
        case 'company':
          $result = $this->instance->setResponseDataType('array')
            ->get("v1/companies/{$this->companyId}/updates");
          break;
      }
    }

    return empty($result['values']) ? [] : $result['values'];
  }

  /**
   * Migrate ids.
   */
  public function getIds() {
    return [
      'updateKey' => [
        'type' => 'string',
        'max_length' => 64,
        'is_ascii' => TRUE,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    if (empty($old_destination_id_values[0])) {
      $body = [
        'content' => [
          'submitted-url' => $row->getDestinationProperty('submitted-url'),
        ],
        'comment' => substr($row->getDestinationProperty('comment'), 0, 699),
        'visibility' => [
          'code' => 'anyone',
        ],
      ];
      $images = $row->getDestinationProperty('submitted-image-url');

      if (!empty($images[0]['url'])) {
        $body['content']['submitted-image-url'] = $images[0]['url'];
      }

      $result = $this->instance->setResponseDataType('array')
        ->post("v1/companies/{$this->companyId}/shares?format=json", [
          'body' => json_encode($body),
        ]);

      if (!empty($result['updateKey'])) {
        return [$result['updateKey']];
      }
    }

    // No updates for linkedin.
    return $old_destination_id_values;
  }
}
