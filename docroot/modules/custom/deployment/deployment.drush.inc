<?php
/**
 * @file
 * Drush integration.
 */

use Drupal\config_import\Drush\Command;

/**
 * Implements hook_drush_command().
 */
function deployment_drush_command() {
  /* @var Command[] $commands */
  $commands = [];

  // Cannot continue without loaded container.
  if (!Drupal::hasContainer()) {
    return $commands;
  }

  $commands['config-import-multiple'] = new Command('confimult', [
    'description' => dt('Import configuration from array.'),
    'options' => [
      'names' => TRUE,
    ],
    'examples' => [
      '--names=views.view.news_events' => dt('Add a description here'),
      '--names=views.view.news_events,core.extension' => dt('Add a description here.'),
    ],
  ]);

  foreach ($commands as $command => $instance) {
    $commands[$command] = $instance->getDefinition();
  }

  return $commands;
}

/**
 * Implements drush_COMMAND().
 */
function drush_deployment_config_import_multiple() {
  try {
    $options = _drush_deployment_get_options();

    // @TODO Validate the names option here.
    $import_confirmed = TRUE;

    if (TRUE == $import_confirmed) {
      // Inspired by deployment_import_configs.
      $importer = Drupal::service('config_import.importer');
      // Set storage where configs located.
      $importer->setDirectory(CONFIG_SYNC_DIRECTORY);

      $configs = (array) explode(',', $options['names']);
      // For new one configs.
      $importer->importConfigs($configs);
      $directory = $importer->getDirectory();

      // For override old one configs.
      foreach ($configs as $config) {
        Drupal::service('config_devel.auto_import_subscriber')->importOne("$directory/$config.yml");
      }

      // @TODO Add success message.
    }
    else {
      // @TODO Add error message.
    }

    drush_log('Import was finished successful.', 'status');
  }
  catch (Exception $e) {
    drush_log($e->getMessage(), 'error');
  }
}

/**
 * Get values for specified options.
 *
 * @return string[]
 *   An associative array where keys - are option names and values - values.
 *
 * @internal
 */
function _drush_deployment_get_options() {
  $command = drush_get_command();
  $options = [];

  foreach ($command['options'] as $option => $specification) {
    $options[$option] = drush_get_option($option);
  }

  // @see Command::processOptions()
  return call_user_func([$command['class'], 'processOptions'], $options);
}
