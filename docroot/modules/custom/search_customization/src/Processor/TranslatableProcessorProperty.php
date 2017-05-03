<?php

namespace Drupal\search_customization\Processor;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataReferenceDefinition;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Processor\ProcessorPropertyInterface;

/**
 *
 */
class TranslatableProcessorProperty extends ProcessorProperty {
  public function getPropertyDefinition() {
    $definition = DataReferenceDefinition::create('entity');
    $definition->setTargetDefinition(\Drupal::typedDataManager()->createDataDefinition('entity:group'));
    return $definition;
  }
}
