<?php

namespace Drupal\taxonomy_image_formatter\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\image_widget_crop\Plugin\Field\FieldWidget\ImageCropWidget;
use Drupal\taxonomy_image_formatter\TaxonomyImageTrait;
use Drupal\user\Entity\User;

/**
 * Plugin implementation of the 'taxonomy_image_widget_crop' widget.
 *
 * @FieldWidget(
 *   id = "taxonomy_image_widget_crop",
 *   label = @Translation("Taxonomy Image Widget crop"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class TaxonomyImageCropWidget extends ImageCropWidget {

  use TaxonomyImageTrait;

  /**
   * {@inheritdoc}
   *
   * @return array<string,array>
   *   The form elements for a single widget for this field.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $entity = $items->getEntity();
    // Default image.
    if ($entity instanceof User) {
      $term_image = $this->getTermImage($entity);

      if ($term_image) {
        $term_image = $term_image->getValue();
        $term_image_file = File::load($term_image['target_id']);
        $element['#default_image']['fid'] = $term_image_file->get('fid')->value;
        $element['#default_image']['uuid'] = $term_image_file->get('uuid')->value;
      }
    }

    return $element;
  }
}
