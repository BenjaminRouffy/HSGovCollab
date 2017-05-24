<?php

namespace Drupal\events\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'string' formatter.
 *
 * @FieldFormatter(
 *   id = "author_string",
 *   label = @Translation("Author formatter"),
 *   field_types = {
 *     "string",
 *     "uri",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class AuthorFormatter extends StringFormatter  {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $real_name = $items->getEntity()->getOwner()->label();

    if ($items->isEmpty()) {
      $elements[0] = [
        '#type' => 'inline_template',
        '#template' => '{{ value|nl2br }}',
        '#context' => ['value' => $real_name],
      ];
    }
    else {
      foreach ($items as $delta => $item) {
        $view_value = $this->viewValue($item);
        if (empty($view_value)) {
          $elements[$delta] = $real_name;
        }
        else {
          $elements[$delta] = $view_value;
        }
      }
    }

    return $elements;
  }

}
