<?php

/**
 * @file
 * Script to add full_name field to User entity.
 *
 * This adds a field_full_name to allow users to set a display name
 * separate from their username (which is their email).
 *
 * Usage: ddev drush php:script scripts/add-user-full-name-field.php
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

echo "=== ADDING FULL NAME FIELD TO USER ENTITY ===\n\n";

$field_name = 'field_full_name';

// Step 1: Create field storage
echo "Step 1: Creating field storage for {$field_name}...\n";

$field_storage = FieldStorageConfig::loadByName('user', $field_name);

if ($field_storage) {
  echo "✓ Field storage '{$field_name}' already exists.\n";
} else {
  $field_storage = FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'user',
    'type' => 'string',
    'cardinality' => 1,
    'settings' => [
      'max_length' => 255,
    ],
  ]);
  $field_storage->save();
  echo "✓ Created field storage '{$field_name}'.\n";
}

// Step 2: Attach field to user entity
echo "\nStep 2: Attaching field to user entity...\n";

$field_config = FieldConfig::loadByName('user', 'user', $field_name);

if ($field_config) {
  echo "✓ Field '{$field_name}' already attached to user entity.\n";
} else {
  $field_config = FieldConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'user',
    'bundle' => 'user',
    'label' => 'Full Name',
    'required' => FALSE,
    'description' => 'Full display name of the user',
    'default_value' => [],
    'settings' => [],
  ]);
  $field_config->save();
  echo "✓ Attached field '{$field_name}' to user entity.\n";
}

// Step 3: Configure form display
echo "\nStep 3: Configuring form display...\n";

/** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
$display_repository = \Drupal::service('entity_display.repository');

$form_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('user.user.default');

if ($form_display) {
  $form_display->setComponent($field_name, [
    'type' => 'string_textfield',
    'weight' => -5,
    'region' => 'content',
    'settings' => [
      'size' => 60,
      'placeholder' => 'e.g., John Doe',
    ],
  ]);
  $form_display->save();
  echo "✓ Configured form display for '{$field_name}'.\n";
} else {
  echo "⚠ Could not load default form display.\n";
}

// Step 4: Configure view display
echo "\nStep 4: Configuring view display...\n";

$view_display = \Drupal::entityTypeManager()
  ->getStorage('entity_view_display')
  ->load('user.user.default');

if ($view_display) {
  $view_display->setComponent($field_name, [
    'type' => 'string',
    'weight' => -5,
    'region' => 'content',
    'label' => 'above',
  ]);
  $view_display->save();
  echo "✓ Configured view display for '{$field_name}'.\n";
} else {
  echo "⚠ Could not load default view display.\n";
}

// Step 5: Clear caches
echo "\nStep 5: Clearing caches...\n";
drupal_flush_all_caches();
echo "✓ Caches cleared.\n";

echo "\n=== FULL NAME FIELD SETUP COMPLETE ===\n";
echo "Users can now set a display name separate from their username (email).\n";
