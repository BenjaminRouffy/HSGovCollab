<?php

namespace Drupal\events\Encoder;

use Drupal\Core\Datetime\DrupalDateTime;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Drupal\p4h_views_plugins\Plugin\ComputedDate\Events;

/**
 * Adds CSV encoder support for the Serialization API.
 */
class ICalEncoder implements EncoderInterface, DecoderInterface {

  protected $vCalendar;

  /**
   *
   */
  public function __construct() {
    $config = \Drupal::config('system.site');
    $this->vCalendar = new Calendar($config->get('name'));
  }

  /**
   * Decodes a string into PHP data.
   *
   * @param string $data
   *   Data to decode.
   * @param string $format
   *   Format name.
   * @param array $context
   *   options that decoders have access to
   *
   *   The format parameter specifies which format the data is in; valid values
   *   depend on the specific implementation. Authors implementing this interface
   *   are encouraged to document which formats they support in a non-inherited
   *   phpdoc comment.
   *
   * @return mixed
   *
   * @throws UnexpectedValueException
   */
  public function decode($data, $format, array $context = []) {
    // TODO: Implement decode() method.
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data, $format, array $context = []) {
    foreach ($data as $row) {
      /** @var \Drupal\node\NodeInterface $node */
      if (isset($row['nid']) && is_numeric($row['nid'])) {
        $node = node_load($row['nid']);
        if (!$node->hasField('field_date')) {
          break;
        }

        if (!isset($node->field_date) || empty($node->field_date->get($row['delta']))) {
          break;
        };

        /** @var \Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem $field */
        $field = $node->field_date->get($row['delta']);

        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
        $start_date = new DrupalDateTime($field->value);
        /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
        $end_date = new DrupalDateTime($field->end_date);

        $vEvent = new Event();
        $vEvent
          ->setDescription(\Drupal::url('entity.node.canonical', [
            'node' => $node->id(),
          ], ['absolute' => TRUE]))
          ->setDtStart(new \DateTime($start_date->format("Y-m-d")))
          ->setDtEnd(new \DateTime($end_date->format("Y-m-d")))
          ->setNoTime(TRUE)
          ->setSummary($row['title']);
        $this->vCalendar->addComponent($vEvent);
      }

    }
    return $this->vCalendar->__toString();
  }

  /**
   * Checks whether the deserializer can decode from given format.
   *
   * @param string $format
   *   format name.
   *
   * @return bool
   */
  public function supportsDecoding($format) {
    // TODO: Implement supportsDecoding() method.
  }

  /**
   * Checks whether the serializer can encode to given format.
   *
   * @param string $format
   *   format name.
   *
   * @return bool
   */
  public function supportsEncoding($format) {
    return TRUE;
  }

}
