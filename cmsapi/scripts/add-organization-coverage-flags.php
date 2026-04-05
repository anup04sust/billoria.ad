<?php

/**
 * @file
 * Adds organization coverage flags for nationwide and international service.
 *
 * Usage: ddev drush php:script scripts/add-organization-coverage-flags.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

$fields = [
  'field_nationwide_service' => [
    'label' => 'Nationwide Service',
    'description' => 'Organization provides service nationwide.',
  ],
  'field_international_service' => [
    'label' => 'International Service',
    'description' => 'Organization provides service internationally.',
  ],
];

foreach ($fields as $field_name => $meta) {
  $storage = FieldStorageConfig::loadByName('node', $field_name);
  if (!$storage) {
    $storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'boolean',
      'cardinality' => 1,
    ]);
    $storage->save();
    echo "Created storage for {$field_name}\n";
  }

  $field = FieldConfig::loadByName('node', 'organization', $field_name);
  if (!$field) {
    $field = FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'bundle' => 'organization',
      'label' => $meta['label'],
      'description' => $meta['description'],
      'required' => FALSE,
    ]);
    $field->save();
    echo "Attached {$field_name} to organization bundle\n";
  }
}

drupal_flush_all_caches();
echo "Done\n";
