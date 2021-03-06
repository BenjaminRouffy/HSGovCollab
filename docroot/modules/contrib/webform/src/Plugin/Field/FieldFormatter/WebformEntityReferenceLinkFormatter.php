<?php

namespace Drupal\webform\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\webform\WebformMessageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Link to webform' formatter.
 *
 * @FieldFormatter(
 *   id = "webform_entity_reference_link",
 *   label = @Translation("Link to form"),
 *   description = @Translation("Display link to the referenced webform."),
 *   field_types = {
 *     "webform"
 *   }
 * )
 */
class WebformEntityReferenceLinkFormatter extends EntityReferenceFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The message manager.
   *
   * @var \Drupal\webform\WebformMessageManagerInterface
   */
  protected $messageManager;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * WebformEntityReferenceLinkFormatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\webform\WebformMessageManagerInterface $message_manager
   *   The message manager.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, WebformMessageManagerInterface $message_manager, Token $token) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->messageManager = $message_manager;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('webform.message_manager'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'label' => 'Go to [webform:title] webform',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Label: @label', ['@label' => $this->getSetting('label')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['label'] = [
      '#title' => $this->t('Label'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('label'),
      '#required' => TRUE,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $source_entity = $items->getEntity();
    $this->messageManager->setSourceEntity($source_entity);

    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      /** @var \Drupal\webform\WebformInterface $entity */
      if ($entity->id() && $items[$delta]->status) {
        $link_options = [
          'query' => [
            'source_entity_type' => $source_entity->getEntityTypeId(),
            'source_entity_id' => $source_entity->id(),
          ],
        ];
        $elements[$delta] = [
          '#type' => 'link',
          '#title' => $this->token->replace($this->getSetting('label'), [
            'webform' => $entity,
          ]),
          '#url' => $entity->toUrl('canonical', $link_options),
        ];
      }
      else {
        /** @var \Drupal\webform\WebformMessageManagerInterface $message_manager */
        $this->messageManager->setWebform($entity);
        $elements[$delta] = $this->messageManager->build(WebformMessageManagerInterface::FORM_CLOSED_MESSAGE);
      }
    }

    return $elements;
  }

}
