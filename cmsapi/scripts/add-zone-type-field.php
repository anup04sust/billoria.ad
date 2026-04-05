<?php

/**
 * @file
 * Script to add field_zone_type to area_zone vocabulary.
 *
 * Usage: ddev drush php:script scripts/add-zone-type-field.php
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "Adding field_zone_type to area_zone vocabulary...\n\n";

// Create field storage.
$field_storage = FieldStorageConfig::loadByName('taxonomy_term', 'field_zone_type');

if (!$field_storage) {
  $field_storage = FieldStorageConfig::create([
    'field_name' => 'field_zone_type',
    'entity_type' => 'taxonomy_term',
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'commercial' => 'Commercial',
        'transit' => 'Transit',
        'residential_mixed' => 'Residential Mixed',
        'industrial' => 'Industrial',
        'premium_urban' => 'Premium Urban',
        'highway_gateway' => 'Highway Gateway',
        'port_access' => 'Port Access',
        'airport_belt' => 'Airport Belt',
        'institutional' => 'Institutional',
      ],
    ],
  ]);

  try {
    $field_storage->save();
    echo "✓ Created field_zone_type storage\n";
  }
  catch (\Exception $e) {
    echo "✗ Failed to create field storage: {$e->getMessage()}\n";
    exit(1);
  }
}
else {
  echo "✓ field_zone_type storage already exists\n";
}

// Add field to area_zone vocabulary.
$field_config = FieldConfig::loadByName('taxonomy_term', 'area_zone', 'field_zone_type');

if (!$field_config) {
  $field_config = FieldConfig::create([
    'field_name' => 'field_zone_type',
    'entity_type' => 'taxonomy_term',
    'bundle' => 'area_zone',
    'label' => 'Zone Type',
    'required' => FALSE,
    'settings' => [],
  ]);

  try {
    $field_config->save();
    echo "✓ Added field_zone_type to area_zone vocabulary\n";
  }
  catch (\Exception $e) {
    echo "✗ Failed to add field config: {$e->getMessage()}\n";
    exit(1);
  }
}
else {
  echo "✓ field_zone_type already exists on area_zone\n";
}

// Add field_is_active too.
$field_storage_active = FieldStorageConfig::loadByName('taxonomy_term', 'field_is_active');
$field_config_active = FieldConfig::loadByName('taxonomy_term', 'area_zone', 'field_is_active');

if (!$field_config_active) {
  $field_config_active = FieldConfig::create([
    'field_name' => 'field_is_active',
    'entity_type' => 'taxonomy_term',
    'bundle' => 'area_zone',
    'label' => 'Is Active',
    'required' => FALSE,
    'default_value' => [['value' => 1]],
  ]);

  try {
    $field_config_active->save();
    echo "✓ Added field_is_active to area_zone vocabulary\n";
  }
  catch (\Exception $e) {
    echo "✗ Failed to add is_active field: {$e->getMessage()}\n";
  }
}

echo "\n✓ Zone type field setup complete!\n";
