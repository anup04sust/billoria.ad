<?php

/**
 * @file
 * Script to add custom fields to taxonomy vocabularies.
 *
 * Usage: ddev drush php:script scripts/add-taxonomy-fields.php
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

$created_fields = [];
$existing_fields = [];

/**
 * Helper to create a field if it doesn't exist.
 */
function create_taxonomy_field($vocabulary_id, $field_name, $field_label, $field_type, $settings = [], $field_settings = []) {
  global $created_fields, $existing_fields;

  $field_storage_id = "taxonomy_term.$field_name";
  $field_id = "taxonomy_term.$vocabulary_id.$field_name";

  // Check if field storage exists.
  $field_storage = FieldStorageConfig::loadByName('taxonomy_term', $field_name);

  if (!$field_storage) {
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'taxonomy_term',
      'type' => $field_type,
      'cardinality' => $settings['cardinality'] ?? 1,
      'settings' => $settings['storage_settings'] ?? [],
    ]);

    try {
      $field_storage->save();
      echo "  ✓ Created field storage: $field_name\n";
    }
    catch (\Exception $e) {
      echo "  ✗ Failed to create field storage $field_name: {$e->getMessage()}\n";
      return FALSE;
    }
  }

  // Check if field config exists for this vocabulary.
  $field_config = FieldConfig::loadByName('taxonomy_term', $vocabulary_id, $field_name);

  if ($field_config) {
    $existing_fields[] = "$field_label ($vocabulary_id.$field_name)";
    return TRUE;
  }

  // Create field config.
  $field_config = FieldConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'taxonomy_term',
    'bundle' => $vocabulary_id,
    'label' => $field_label,
    'required' => $field_settings['required'] ?? FALSE,
    'settings' => $field_settings['settings'] ?? [],
  ]);

  try {
    $field_config->save();
    $created_fields[] = "$field_label ($vocabulary_id.$field_name)";
    echo "  ✓ Added field: $field_label to $vocabulary_id\n";
    return TRUE;
  }
  catch (\Exception $e) {
    echo "  ✗ Failed to add field $field_name to $vocabulary_id: {$e->getMessage()}\n";
    return FALSE;
  }
}

echo "Adding custom fields to taxonomies...\n\n";

// 1) road_type: Add code and description fields.
echo "road_type vocabulary:\n";
create_taxonomy_field('road_type', 'field_code', 'Code', 'string');
create_taxonomy_field('road_type', 'field_description', 'Description', 'text_long');

// 2) district: Add division reference and geo_code.
echo "\ndistrict vocabulary:\n";
create_taxonomy_field('district', 'field_division', 'Division', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['division' => 'division'],
    ],
  ],
]);
create_taxonomy_field('district', 'field_geo_code', 'Geo Code', 'string');

// 3) upazila_thana: Add district reference, geo_code, and type.
echo "\nupazila_thana vocabulary:\n";
create_taxonomy_field('upazila_thana', 'field_district', 'District', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['district' => 'district'],
    ],
  ],
]);
create_taxonomy_field('upazila_thana', 'field_geo_code', 'Geo Code', 'string');
create_taxonomy_field('upazila_thana', 'field_type', 'Type', 'list_string', [
  'storage_settings' => [
    'allowed_values' => [
      'upazila' => 'Upazila',
      'thana' => 'Thana (Metropolitan)',
      'pourashava' => 'Pourashava',
    ],
  ],
]);

// 4) area_zone: Add upazila_thana, city_corporation, district, and priority_tier.
echo "\narea_zone vocabulary:\n";
create_taxonomy_field('area_zone', 'field_upazila_thana', 'Upazila / Thana', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['upazila_thana' => 'upazila_thana'],
    ],
  ],
]);
create_taxonomy_field('area_zone', 'field_city_corporation', 'City Corporation', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['city_corporation' => 'city_corporation'],
    ],
  ],
]);
create_taxonomy_field('area_zone', 'field_district', 'District', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['district' => 'district'],
    ],
  ],
]);
create_taxonomy_field('area_zone', 'field_priority_tier', 'Priority Tier', 'list_string', [
  'storage_settings' => [
    'allowed_values' => [
      'tier_1' => 'Tier 1',
      'tier_2' => 'Tier 2',
      'tier_3' => 'Tier 3',
    ],
  ],
]);

// 5) road_name: Add all reference fields.
echo "\nroad_name vocabulary:\n";
create_taxonomy_field('road_name', 'field_road_code', 'Road Code', 'string');
create_taxonomy_field('road_name', 'field_road_type', 'Road Type', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['road_type' => 'road_type'],
    ],
  ],
]);
create_taxonomy_field('road_name', 'field_division', 'Division', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['division' => 'division'],
    ],
  ],
]);
create_taxonomy_field('road_name', 'field_district', 'District', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['district' => 'district'],
    ],
  ],
]);
create_taxonomy_field('road_name', 'field_upazila_thana', 'Upazila / Thana', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['upazila_thana' => 'upazila_thana'],
    ],
  ],
]);
create_taxonomy_field('road_name', 'field_city_corporation', 'City Corporation', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['city_corporation' => 'city_corporation'],
    ],
  ],
]);
create_taxonomy_field('road_name', 'field_area_zone', 'Area / Zone', 'entity_reference', [
  'storage_settings' => ['target_type' => 'taxonomy_term'],
], [
  'settings' => [
    'handler' => 'default:taxonomy_term',
    'handler_settings' => [
      'target_bundles' => ['area_zone' => 'area_zone'],
    ],
  ],
]);
create_taxonomy_field('road_name', 'field_priority_tier', 'Priority Tier', 'list_string', [
  'storage_settings' => [
    'allowed_values' => [
      'tier_1' => 'Tier 1',
      'tier_2' => 'Tier 2',
      'tier_3' => 'Tier 3',
    ],
  ],
]);
create_taxonomy_field('road_name', 'field_commercial_score', 'Commercial Score', 'integer', [
  'storage_settings' => [],
], [
  'settings' => ['min' => 1, 'max' => 100],
]);
create_taxonomy_field('road_name', 'field_is_active', 'Active', 'boolean');
create_taxonomy_field('road_name', 'field_notes', 'Notes', 'text_long');
create_taxonomy_field('road_name', 'field_osm_ref', 'OSM Ref', 'string');

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "FIELD CREATION SUMMARY:\n";
echo "Created: " . count($created_fields) . " fields\n";
echo "Already existed: " . count($existing_fields) . " fields\n";

if (!empty($created_fields)) {
  echo "\nNewly created fields:\n";
  foreach ($created_fields as $field) {
    echo "  - $field\n";
  }
}

echo "\n✓ Taxonomy field creation complete!\n";
echo "\nNext step: Import district, upazila_thana, area_zone, and road_name terms with references.\n";
