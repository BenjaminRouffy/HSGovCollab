<?php

namespace Drupal\events\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Render\Element;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeDefaultWidget;

/**
 * Plugin implementation of the 'daterange_eventdaterangelist' widget.
 *
 * @FieldWidget(
 *   id = "daterange_eventdaterangelist",
 *   label = @Translation("Event date range for multiple field"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class EventDateRangeListWidget extends DateRangeDefaultWidget {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Add validation callback to exclude intersects in date ranges.
    $element['#element_validate'][] = [$this, 'validateIntersect'];

    return $element;
  }

  /**
   * Callback to ensure that the date ranges are not intersect.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public function validateIntersect(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $range_values = $form_state->getValue('field_date');
    $ranges = [];

    if (!empty($element['value']['date']['#value'])) {
      foreach ($range_values as $key => $range) {
        if (is_numeric($key)) {
          if ($range['value'] instanceof DrupalDateTime && $range['end_value'] instanceof DrupalDateTime) {
            $ranges[] = $range;
          }
        }
      }

      foreach ($ranges as $key_first => $range_first) {
        foreach ($ranges as $key_second => $range_second) {
          if ($key_first == $key_second) {
            continue;
          }

          if ($range_first['value'] instanceof DrupalDateTime &&
            $range_first['end_value'] instanceof DrupalDateTime &&
            $range_second['value'] instanceof DrupalDateTime &&
            $range_second['end_value'] instanceof DrupalDateTime
          ) {
            if ($range_first['value']->format('Y-m-d') == $range_second['value']->format('Y-m-d') &&
              $range_first['end_value']->format('Y-m-d') == $range_second['end_value']->format('Y-m-d')
            ) {
              $form_state->setError($element, $this->t('The @title range cannot be the same as other ranges', ['@title' => $element['#title']]));
            }

            if ($range_first['value']->getTimestamp() < $range_second['end_value']->getTimestamp() &&
              $range_first['value']->getTimestamp() >= $range_second['value']->getTimestamp()
            ) {
              $form_state->setError($element, $this->t('The @title range cannot intersect with other ranges', ['@title' => $element['#title']]));
            }
          }
        }
      }
    }
  }
}
