<?php

namespace Drupal\social_block\Plugin\Block;

// Core components.
use Drupal\Core\Link;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
// Custom components.
use Drupal\social_block\SocialNetwork\ContentFetcherBase;
use Drupal\social_block\SocialNetwork\ContentFetcherPluginManager;

/**
 * Class SocialBlock.
 *
 * @Block(
 *   id = "social_block",
 *   category = @Translation("Custom"),
 *   admin_label = @Translation("Social block"),
 * )
 */
class SocialBlock extends BlockBase {

  /**
   * An instance of Content Fetcher plugin manager.
   *
   * @var ContentFetcherPluginManager
   */
  private $contentFetcher;
  /**
   * List of content fetcher plugin instances.
   *
   * @var ContentFetcherBase[]
   */
  private $contentFetcherInstances = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->contentFetcher = \Drupal::service('plugin.manager.social_network.content_fetcher');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'count' => 10,
      'networks' => [],
      'accounts' => [],
      'cache_lifetime' => 20,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $selected = $form_state->getValue(['settings', 'networks'], $this->configuration['networks']);
    $available = [];
    $unavailable = [];

    foreach (array_intersect_key(sdk_types(), $this->contentFetcher->getDefinitions()) as $type => $info) {
      try {
        sdk_deriver($type);

        $available[$type] = $info['label'];
      }
      catch (\Exception $e) {
        $unavailable[$type] = $info['label'];
      }
    }

    $form['networks'] = [
      '#type' => 'select',
      '#ajax' => $this->addAjaxWrapper($form, __FUNCTION__, 'reloadSettings'),
      '#title' => $this->t('Networks'),
      '#options' => $available,
      '#required' => TRUE,
      '#multiple' => TRUE,
      '#default_value' => $this->configuration['networks'],
    ];

    $form['count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of posts'),
      '#description' => $this->t('The more social networks will be selected, the less entries from each will be retrieved.'),
      '#default_value' => $this->configuration['count'],
    ];

    if (!empty($selected)) {
      $form['accounts'] = [
        '#tree' => TRUE,
      ];

      foreach ($selected as $type) {
        $form['accounts'][$type] = [
          '#type' => 'textfield',
          '#title' => $this->t('@network account', ['@network' => $available[$type]]),
          '#required' => TRUE,
          '#default_value' => empty($this->configuration['accounts'][$type]) ? '' : $this->configuration['accounts'][$type],
        ];
      }
    }

    if (!empty($unavailable)) {
      $form['unavailable'] = [
        '#markup' => $this->t('The following networks were not configured: @items. You can fix this by adding the configuration @link.', [
          '@items' => implode(', ', $unavailable),
          '@link' => Link::createFromRoute($this->t('here'), 'entity.sdk.collection', [], ['attributes' => ['target' => '_blank']])->toString(),
        ]),
      ];
    }

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
    ];

    $form['advanced']['cache_lifetime'] = [
      '#min' => 0,
      '#type' => 'number',
      '#title' => $this->t('Cache lifetime'),
      '#parents' => ['settings', 'cache_lifetime'],
      '#description' => $this->t('Number of minutes to store results. Setting value to "0" lead to ignoring a cache.'),
      '#default_value' => $this->configuration['cache_lifetime'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reloadSettings(array $form, FormStateInterface $form_state) {
    return $form['settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    foreach ($this->defaultConfiguration() as $key => $default_value) {
      $this->configuration[$key] = $form_state->getValue($key);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = $this->getContent();

    shuffle($content['posts']);
    array_splice($content['posts'], $this->configuration['count']);

    return $content;
  }

  /**
   * Fetched content.
   *
   * @return array[]
   *   An associative array with values in "links" and "posts" keys.
   */
  final public function getContent() {
    $logger_channel = \Drupal::logger('social_block');
    $cache_allowed = FALSE;
    $cache_bin = \Drupal::cache();
    $content = [
      'links' => [],
      'posts' => [],
    ];

    foreach ($this->configuration['networks'] as $network) {
      $cache_id = static::class . '::' . $network;
      $cache_item = $cache_bin->get($cache_id);

      if (!$cache_allowed || FALSE === $cache_item || TRUE) {
        try {
          $fetcher = $this->getContentFetcherInstance($network);

          $cache_data['posts'] = $fetcher->getItems();
          $cache_data['link'] = $fetcher->getProfileLink();
        }
        catch (\Exception $e) {
          $logger_channel->warning($this->t('The problem appears with @network. Exception message: @message.', [
            '@network' => ucfirst($network),
            '@message' => $e->getMessage(),
          ]));

          continue;
        }

        if ($cache_allowed && !empty($cache_data['posts'])) {
          $cache_bin->set($cache_id, $cache_data, strtotime(sprintf('+ %s minute', $this->configuration['cache_lifetime'])), $this->getCacheTags());
        }
      }
      else {
        $cache_data = $cache_item->data;
      }

      $content['posts'] = array_merge($content['posts'], $cache_data['posts']);
      $content['links'][$network] = $cache_data['link'];
    }

    return $content;
  }

  /**
   * Instantiate content fetcher.
   *
   * @param string $network
   *   Machine name of a social network.
   *
   * @return ContentFetcherBase
   *   An instance of content fetcher.
   */
  final protected function getContentFetcherInstance($network) {
    if (empty($this->contentFetcherInstances[$network])) {
      $this->contentFetcherInstances[$network] = $this->contentFetcher->createInstance($network, $this->configuration);
    }

    return $this->contentFetcherInstances[$network];
  }

  /**
   * Wrap group of elements into additional container.
   *
   * @param array $element
   *   Element definition.
   * @param string $id
   *   An ID of a wrapper.
   * @param string $method
   *   Method of instance of this block.
   *
   * @return array
   *   Definition of "#ajax" for element.
   */
  final protected function addAjaxWrapper(array &$element, $id, $method) {
    $element['#prefix'] = '<div id="' . $id . '">';
    $element['#suffix'] = '</div>';

    return [
      'callback' => [$this, $method],
      'wrapper' => $id,
    ];
  }

}
