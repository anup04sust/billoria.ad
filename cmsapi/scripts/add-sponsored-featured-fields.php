<?php

/**
 * @file
 * Adds field_is_sponsored and field_is_featured boolean fields to the Billboard content type.
 *
 * These fields are admin-only: only platform_admin and administrator roles can edit them.
 *
 * Run: ddev drush scr scripts/add-sponsored-featured-fields.php
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\user\Entity\Role;

echo "Adding Sponsored and Featured fields to Billboard content type...\n\n";

// --- field_is_sponsored ---
if (!FieldStorageConfig::loadByName('node', 'field_is_sponsored')) {
  FieldStorageConfig::create([
    'field_name' => 'field_is_sponsored',
    'entity_type' => 'node',
    'type' => 'boolean',
    'cardinality' => 1,
  ])->save();
  echo "✓ field_is_sponsored storage created\n";
} else {
  echo "• field_is_sponsored storage already exists\n";
}

if (!FieldConfig::loadByName('node', 'billboard', 'field_is_sponsored')) {
  FieldConfig::create([
    'field_name' => 'field_is_sponsored',
    'entity_type' => 'node',
    'bundle' => 'billboard',
    'label' => 'Sponsored',
    'required' => FALSE,
    'description' => 'Mark this billboard as a sponsored listing. Only administrators can edit this field.',
    'default_value' => [['value' => 0]],
  ])->save();
  echo "✓ field_is_sponsored instance created\n";
} else {
  echo "• field_is_sponsored instance already exists\n";
}

// --- field_is_featured ---
if (!FieldStorageConfig::loadByName('node', 'field_is_featured')) {
  FieldStorageConfig::create([
    'field_name' => 'field_is_featured',
    'entity_type' => 'node',
    'type' => 'boolean',
    'cardinality' => 1,
  ])->save();
  echo "✓ field_is_featured storage created\n";
} else {
  echo "• field_is_featured storage already exists\n";
}

if (!FieldConfig::loadByName('node', 'billboard', 'field_is_featured')) {
  FieldConfig::create([
    'field_name' => 'field_is_featured',
    'entity_type' => 'node',
    'bundle' => 'billboard',
    'label' => 'Featured',
    'required' => FALSE,
    'description' => 'Mark this billboard as a featured listing. Only administrators can edit this field.',
    'default_value' => [['value' => 0]],
  ])->save();
  echo "✓ field_is_featured instance created\n";
} else {
  echo "• field_is_featured instance already exists\n";
}

echo "\n✅ Fields added successfully!\n";
echo "\nNext: Run 'ddev drush cr' to clear cache.\n";
echo "Admin fields will be restricted via hook_form_alter in billoria_core.module.\n";
