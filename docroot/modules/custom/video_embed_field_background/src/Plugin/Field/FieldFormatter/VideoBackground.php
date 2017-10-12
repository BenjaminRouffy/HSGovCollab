<?php

namespace Drupal\video_embed_field_background\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Video;
use Drupal\video_embed_field\ProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the video field formatter.
 *
 * @FieldFormatter(
 *   id = "video_embed_field_video_background",
 *   label = @Translation("Video Background"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class VideoBackground extends Video implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $provider = $this->providerManager->loadProviderFromInput($item->value);

      if (!$provider) {
        $element[$delta] = ['#theme' => 'video_embed_field_missing_provider'];
      }
      else {
        $autoplay = $this->currentUser->hasPermission('never autoplay videos') ? FALSE : $this->getSetting('autoplay');
        $element[$delta] = $provider->renderEmbedCode($this->getSetting('width'), $this->getSetting('height'), $autoplay);
        $element[$delta]['#type'] = 'video_embed_background';
        $element[$delta]['#cache']['contexts'][] = 'user.permissions';

        // For responsive videos, wrap each field item in it's own container.
        if ($this->getSetting('responsive')) {
          $element[$delta] = [
            '#type' => 'container',
            '#attached' => ['library' => ['video_embed_field/responsive-video']],
            '#attributes' => ['class' => ['video-embed-field-responsive-video']],
            'children' => $element[$delta],
          ];
        }
      }

    }
    return $element;
  }

}
