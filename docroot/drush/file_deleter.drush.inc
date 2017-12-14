<?php

/**
 * @file
 * File Deleter drush commands.
 */

use Drupal\file\Entity\File;

/**
 * Implements hook_drush_command().
 */
function file_deleter_drush_command() {

  $commands['files-del-missing'] = [
    'description' => 'Delete missing file entities.',
    'arguments' => [
      'count' => 'Number to delete.',
      'start' => 'Starting fid to check.',
      'not_kidding' => 'Set to TRUE if you are not kidding.',
    ],
  ];

  return $commands;
}

/**
 * Drush logic for command files-del-missing.
 */
function drush_file_deleter_files_del_missing($start = 1, $count = 5, $not_kidding = FALSE) {
  drush_print(dt("Deleting files"));

  $query = \Drupal::entityQuery('file');
  $query->condition('fid', $start, '>=');
  $query->range(0, $count);
  $files = $query->execute();
  foreach ($files as $fid) {
    $file = File::load($fid);
    $uri = $file->uri->value;
    if (!is_file($uri)) {
      if ($not_kidding) {
        drush_print("Deleting file: {$file->id()} - {$file->uri->value}");
        $file->delete();
      }
      else {
        drush_print("File is missing: {$file->id()} - {$file->uri->value}");
      }
    }
    else {
      drush_print("File is not missing: {$file->id()} - {$file->uri->value}");
    }
  }

}
