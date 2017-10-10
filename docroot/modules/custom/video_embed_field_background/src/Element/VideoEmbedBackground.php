<?php

namespace Drupal\video_embed_field_background\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Template\Attribute;
use Drupal\video_embed_field\Element\VideoEmbedIFrame;

/**
 * Providers an element design for embedding background.
 *
 * @RenderElement("video_embed_background")
 */
class VideoEmbedBackground extends VideoEmbedIFrame {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#theme' => 'video_embed_background',
      '#provider' => '',
      '#url' => '',
      '#query' => [],
      '#attributes' => [],
      '#fragment' => [],
      '#pre_render' => [
        [static::class, 'preRenderInlineFrameEmbed'],
      ],
    ];
  }
}
