<?php

namespace Drupal\migrate_social;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Database\Database;
use Drupal\migrate\Row;
use Drupal\migrate_social\SocialNetworkInterface;

/**
 * A base class to help developers implement their own plugins.
 *
 * @see \Drupal\migrate_social\Annotation\RelatedContent
 * @see \Drupal\migrate_social\RelatedContentInterface
 */
abstract class SocialNetworkBase extends PluginBase implements SocialNetworkInterface {
  protected $currentItem;
  protected $instance;
  protected $iterator;

  /**
   * @inheritdoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->instance = sdk($this->pluginId);
  }

  /**
   * {@inheritdoc}
   */
  public function description() {
    // Retrieve the @description property from the annotation and return it.
    return $this->pluginDefinition['description'];
  }


  /**
   * Implementation of Iterator::next().
   */
  public function next() {
    $this->currentItem = $this->currentId = NULL;
    if (empty($this->iterator)) {
      if (!$this->nextSource()) {
        // No data to import.
        return;
      }
    }
    // At this point, we have a valid open source url, try to fetch a row from
    // it.
    $this->fetchNextRow();
  }

  /**
   * {@inheritdoc}
   */
  protected function fetchNextRow() {
    $current = $this->iterator->current();
     if ($current) {
       foreach ($current as $field_name => $field_date) {
         $this->currentItem[$field_name] = $field_date;
       }
      $this->iterator->next();
    }
  }

  /**
   * Advances the data parser to the next source url.
   *
   * @return bool
   *   TRUE if a valid source URL was opened
   */
  private function nextSource() {
    $values = $this->getSocialRows();

    if (empty($values)) {
      return FALSE;
    }

    $posts = [];

    if (!empty($this->configuration['autopost_migrations'])) {
      $autopost_results = [];

      foreach ($this->configuration['autopost_migrations'] as $migration) {
        $autopost_results += Database::getConnection('default')
          ->select("migrate_map_$migration", 'l')
          ->fields('l', ['destid1'])
          ->execute()
          ->fetchAllAssoc('destid1');
      }
      $ids = $this->getIds();
      $id = key($ids);
      foreach ($values as $post) {
        if (isset($autopost_results[$post[$id]])) {
          continue;
        }

        $posts[] = $post;
      }
    }
    else {
      $posts = $values;
    }

    $this->iterator = new \ArrayIterator($posts);
    return TRUE;
  }

  /**
   * Get data from social.
   */
  abstract protected function getSocialRows();

  /**
   * Import/update one row to social network.
   */
  abstract public function import(Row $row, array $old_destination_id_values = []);

  /**
   * {@inheritdoc}
   */
  public function current() {
    return $this->currentItem;
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->currentId;
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return !empty($this->currentItem);
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->activeUrl = NULL;
    $this->next();
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    if (empty($this->iterator)) {
      if (!$this->nextSource()) {
        // No data to import.
        return -1;
      }
    }
    return $this->iterator->count();
  }

  /**
   * Migrate ids.
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'string',
        'max_length' => 64,
        'is_ascii' => TRUE,
      ],
    ];
  }
}
