<?php

/**
 * @file
 * Script to configure form display for taxonomy vocabularies.
 *
 * Usage: ddev drush php:script scripts/configure-taxonomy-form-display.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;

$vocabularies = [
  'area_zone' => [
    'field_district' => ['type' => 'entity_reference_autocomplete', 'weight' => 1],
    'field_upazila_thana' => ['type' => 'entity_reference_autocomplete', 'weight' => 2],
    'field_city_corporation' => ['type' => 'entity_reference_autocomplete', 'weight' => 3],
    'field_priority_tier' => ['type' => 'options_select', 'weight' => 4],
    'field_zone_type' => ['type' => 'options_select', 'weight' => 5],
    'field_is_active' => ['type' => 'boolean_checkbox', 'weight' => 6],
  ],
  'district' => [
    'field_division' => ['type' => 'entity_reference_autocomplete', 'weight' => 1],
    'field_geo_code' => ['type' => 'string_textfield', 'weight' => 2],
  ],
  'upazila_thana' => [
    'field_district' => ['type' => 'entity_reference_autocomplete', 'weight' => 1],
    'field_geo_code' => ['type' => 'string_textfield', 'weight' => 2],
    'field_type' => ['type' => 'options_select', 'weight' => 3],
  ],
  'road_type' => [
    'field_code' => ['type' => 'string_textfield', 'weight' => 1],
    'field_description' => ['type' => 'text_textarea', 'weight' => 2],
  ],
  'road_name' => [
    'field_road_code' => ['type' => 'string_textfield', 'weight' => 1],
    'field_road_type' => ['type' => 'entity_reference_autocomplete', 'weight' => 2],
    'field_division' => ['type' => 'entity_reference_autocomplete', 'weight' => 3],
    'field_district' => ['type' => 'entity_reference_autocomplete', 'weight' => 4],
    'field_upazila_thana' => ['type' => 'entity_reference_autocomplete', 'weight' => 5],
    'field_city_corporation' => ['type' => 'entity_reference_autocomplete', 'weight' => 6],
    'field_area_zone' => ['type' => 'entity_reference_autocomplete', 'weight' => 7],
    'field_priority_tier' => ['type' => 'options_select', 'weight' => 8],
    'field_commercial_score' => ['type' => 'number', 'weight' => 9],
    'field_is_active' => ['type' => 'boolean_checkbox', 'weight' => 10],
    'field_notes' => ['type' => 'text_textarea', 'weight' => 11],
    'field_osm_ref' => ['type' => 'string_textfield', 'weight' => 12],
  ],
];

$configured = 0;
$skipped = 0;

echo "Configuring form displays for taxonomy vocabularies...\n\n";

foreach ($vocabularies as $vocabulary_id => $fields) {
  echo "Configuring $vocabulary_id form display...\n";

  // Load or create form display.
  $form_display = EntityFormDisplay::load("taxonomy_term.$vocabulary_id.default");

  if (!$form_display) {
    $form_display = EntityFormDisplay::create([
      'targetEntityType' => 'taxonomy_term',
      'bundle' => $vocabulary_id,
      'mode' => 'default',
      'status' => TRUE,
    ]);
  }

  // Configure each field.
  foreach ($fields as $field_name => $config) {
    $form_display->setComponent($field_name, [
      'type' => $config['type'],
      'weight' => $config['weight'],
      'region' => 'content',
    ]);
    echo "  ✓ Configured: $field_name\n";
  }

  // Save form display.
  try {
    $form_display->save();
    $configured++;
    echo "✓ Saved form display for $vocabulary_id\n\n";
  }
  catch (\Exception $e) {
    $skipped++;
    echo "✗ Failed to save form display for $vocabulary_id: {$e->getMessage()}\n\n";
  }
}

// Summary.
echo str_repeat('=', 50) . "\n";
echo "FORM DISPLAY CONFIGURATION SUMMARY:\n";
echo "Configured: $configured vocabularies\n";
echo "Failed: $skipped vocabularies\n";
echo "\n✓ Form display configuration complete!\n";
echo "\nNow all relational fields should be visible when editing taxonomy terms.\n";
