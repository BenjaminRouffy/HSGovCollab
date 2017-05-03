<?php

namespace Drupal\search_customization\Plugin\facets_summary\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\facets_summary\FacetsSummaryInterface;
use Drupal\facets_summary\Plugin\facets_summary\processor\ResetFacetsProcessor;
use Drupal\facets_summary\Processor\BuildProcessorInterface;
use Drupal\facets_summary\Processor\ProcessorPluginBase;

/**
 * Provides a processor that allows to reset facet filters.
 *
 * @SummaryProcessor(
 *   id = "only_reset_facets",
 *   label = @Translation("Only reset link."),
 *   description = @Translation("When checked, this facet will show only a link to reset enabled facets."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class OnlyResetFacetsProcessor extends ResetFacetsProcessor  {

  /**
   * {@inheritdoc}
   */
  public function build(FacetsSummaryInterface $facets_summary, array $build, array $facets) {
    $conf = $facets_summary->getProcessorConfigs()[$this->getPluginId()];

    // Do nothing if there are no selected facets or reset text is empty.
    if (empty($build['#items']) || empty($conf['settings']['link_text'])) {
      return $build;
    }

    $request = \Drupal::requestStack()->getMasterRequest();
    $query_params = $request->query->all();

    // Lets use any first facet to get correct url.
    $results = [];
    // Bypass all active facets and remove them from the query parameters array.
    foreach ($facets as $facet) {
      $url_alias = $facet->getUrlAlias();
      $filter_key = $facet->getFacetSourceConfig()->getFilterKey() ?: 'f';

      if (isset($query_params[$filter_key])) {
        foreach ($query_params[$filter_key] as $delta => $param) {
          // Only if it starts with selected needle.
          if (strpos($param, $url_alias . ':') === 0) {
            $results = $facet->getResults();
            unset($query_params[$filter_key][$delta]);
          }
        }

        if (!$query_params[$filter_key]) {
          unset($query_params[$filter_key]);
        }
      }
    }

    /** @var  \Drupal\Core\Url $first_item_url */
    $first_item_url = reset($results)->getUrl();
    $first_item_url = clone ($first_item_url);
    $first_item_url->setOptions(['query' => $query_params]);

    $item = (new Link($conf['settings']['link_text'], $first_item_url))->toRenderable();

    $build['#items'] = [$item];
    return $build;
  }

}
