<?php

/**
 * @file
 * Creates the Billboard content type with all 40+ fields.
 *
 * Run: ddev drush scr scripts/create-billboard-content-type.php
 */

use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

echo "Creating Billboard content type...\n\n";

// Create the content type.
$node_type = NodeType::create([
  'type' => 'billboard',
  'name' => 'Billboard',
  'description' => 'Individual billboard or display asset inventory',
  'title_label' => 'Billboard Name',
  'display_submitted' => FALSE,
]);
$node_type->save();
echo "✓ Billboard content type created\n";

// Helper function to create field storage
function create_field_storage($field_name, $field_type, $cardinality = 1, $settings = []) {
  if (!FieldStorageConfig::loadByName('node', $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => $field_type,
      'cardinality' => $cardinality,
      'settings' => $settings,
    ])->save();
    return TRUE;
  }
  return FALSE;
}

// Helper function to create field instance
function create_field_instance($field_name, $bundle, $label, $required = FALSE, $settings = []) {
  if (!FieldConfig::loadByName('node', $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'bundle' => $bundle,
      'label' => $label,
      'required' => $required,
      'settings' => $settings,
    ])->save();
    return TRUE;
  }
  return FALSE;
}

echo "\nCreating field storages...\n";

// 1. Billboard ID - Plain text
create_field_storage('field_billboard_id', 'string');
create_field_instance('field_billboard_id', 'billboard', 'Billboard ID', TRUE);
echo "✓ field_billboard_id\n";

// 2. Media Format - Term reference
create_field_storage('field_media_format', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_media_format', 'billboard', 'Media Format', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['media_format' => 'media_format'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_media_format\n";

// 3. Placement Type - Term reference
create_field_storage('field_placement_type', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_placement_type', 'billboard', 'Placement Type', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['placement_type' => 'placement_type'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_placement_type\n";

// 4. Road Name - Term reference
create_field_storage('field_road_name', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_road_name', 'billboard', 'Road Name', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['road_name' => 'road_name'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_road_name\n";

// 5. Road Type - Term reference
create_field_storage('field_road_type', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_road_type', 'billboard', 'Road Type', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['road_type' => 'road_type'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_road_type\n";

// 6. Division - Term reference
create_field_storage('field_division', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_division', 'billboard', 'Division', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['division' => 'division'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_division\n";

// 7. District - Term reference
create_field_storage('field_district', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_district', 'billboard', 'District', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['district' => 'district'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_district\n";

// 8. Upazila/Thana - Term reference
create_field_storage('field_upazila_thana', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_upazila_thana', 'billboard', 'Upazila / Thana', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['upazila_thana' => 'upazila_thana'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_upazila_thana\n";

// 9. City Corporation - Term reference
create_field_storage('field_city_corporation', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_city_corporation', 'billboard', 'City Corporation', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['city_corporation' => 'city_corporation'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_city_corporation\n";

// 10. Area/Zone - Term reference
create_field_storage('field_area_zone', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_area_zone', 'billboard', 'Area / Zone', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['area_zone' => 'area_zone'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_area_zone\n";

// 11. Traffic Direction - Term reference
create_field_storage('field_traffic_direction', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_traffic_direction', 'billboard', 'Traffic Direction', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['traffic_direction' => 'traffic_direction'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_traffic_direction\n";

// 12. Visibility Class - Term reference
create_field_storage('field_visibility_class', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_visibility_class', 'billboard', 'Visibility Class', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['visibility_class' => 'visibility_class'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_visibility_class\n";

// 13. Illumination Type - Term reference
create_field_storage('field_illumination_type', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_illumination_type', 'billboard', 'Illumination Type', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['illumination_type' => 'illumination_type'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_illumination_type\n";

// 14. Booking Mode - Term reference
create_field_storage('field_booking_mode', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_booking_mode', 'billboard', 'Booking Mode', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['booking_mode' => 'booking_mode'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_booking_mode\n";

// 15. Availability Status - Term reference
create_field_storage('field_availability_status', 'entity_reference', 1, [
  'target_type' => 'taxonomy_term',
]);
create_field_instance('field_availability_status', 'billboard', 'Availability Status', TRUE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => [
    'target_bundles' => ['availability_status' => 'availability_status'],
    'auto_create' => FALSE,
  ],
]);
echo "✓ field_availability_status\n";

// 16. Latitude - Decimal
create_field_storage('field_latitude', 'decimal', 1, [
  'precision' => 10,
  'scale' => 7,
]);
create_field_instance('field_latitude', 'billboard', 'Latitude', TRUE, [
  'description' => 'GPS latitude (e.g., 23.8103)',
]);
echo "✓ field_latitude\n";

// 17. Longitude - Decimal
create_field_storage('field_longitude', 'decimal', 1, [
  'precision' => 10,
  'scale' => 7,
]);
create_field_instance('field_longitude', 'billboard', 'Longitude', TRUE, [
  'description' => 'GPS longitude (e.g., 90.4125)',
]);
echo "✓ field_longitude\n";

// 18. Facing Direction - List (text)
create_field_storage('field_facing_direction', 'list_string');
create_field_instance('field_facing_direction', 'billboard', 'Facing Direction', FALSE, [
  'settings' => [
    'allowed_values' => [
      'north' => 'North',
      'south' => 'South',
      'east' => 'East',
      'west' => 'West',
      'north_east' => 'North East',
      'north_west' => 'North West',
      'south_east' => 'South East',
      'south_west' => 'South West',
    ],
  ],
]);
echo "✓ field_facing_direction\n";

// 19. Visibility Distance (m) - Integer
create_field_storage('field_visibility_distance', 'integer');
create_field_instance('field_visibility_distance', 'billboard', 'Visibility Distance (m)', FALSE, [
  'description' => 'Estimated visibility distance in meters',
]);
echo "✓ field_visibility_distance\n";

// 20. Width (ft) - Decimal
create_field_storage('field_width_ft', 'decimal', 1, [
  'precision' => 5,
  'scale' => 2,
]);
create_field_instance('field_width_ft', 'billboard', 'Width (ft)', FALSE);
echo "✓ field_width_ft\n";

// 21. Height (ft) - Decimal
create_field_storage('field_height_ft', 'decimal', 1, [
  'precision' => 5,
  'scale' => 2,
]);
create_field_instance('field_height_ft', 'billboard', 'Height (ft)', FALSE);
echo "✓ field_height_ft\n";

// 22. Display Size Text - Plain text
create_field_storage('field_display_size', 'string');
create_field_instance('field_display_size', 'billboard', 'Display Size', FALSE, [
  'description' => 'Example: 10x20 ft or 20x30 ft',
]);
echo "✓ field_display_size\n";

// 23. Lane Count - Integer
create_field_storage('field_lane_count', 'integer');
create_field_instance('field_lane_count', 'billboard', 'Lane Count', FALSE, [
  'description' => 'Number of lanes on nearby road',
]);
echo "✓ field_lane_count\n";

// 24. Has Divider - Boolean
create_field_storage('field_has_divider', 'boolean');
create_field_instance('field_has_divider', 'billboard', 'Has Divider', FALSE);
echo "✓ field_has_divider\n";

// 25. Commercial Score - Integer
create_field_storage('field_commercial_score', 'integer');
create_field_instance('field_commercial_score', 'billboard', 'Commercial Score', FALSE, [
  'description' => 'Internal ranking (1-100)',
]);
echo "✓ field_commercial_score\n";

// 26. Traffic Score - Integer
create_field_storage('field_traffic_score', 'integer');
create_field_instance('field_traffic_score', 'billboard', 'Traffic Score', FALSE, [
  'description' => 'Internal ranking (1-100)',
]);
echo "✓ field_traffic_score\n";

// 27. Rate Card Price - Decimal
create_field_storage('field_rate_card_price', 'decimal', 1, [
  'precision' => 10,
  'scale' => 2,
]);
create_field_instance('field_rate_card_price', 'billboard', 'Rate Card Price', FALSE, [
  'description' => 'Base commercial rate (monthly)',
]);
echo "✓ field_rate_card_price\n";

// 28. Currency - List (text)
create_field_storage('field_currency', 'list_string');
create_field_instance('field_currency', 'billboard', 'Currency', FALSE, [
  'settings' => [
    'allowed_values' => [
      'BDT' => 'BDT (Taka)',
      'USD' => 'USD (Dollar)',
    ],
  ],
  'default_value' => [['value' => 'BDT']],
]);
echo "✓ field_currency\n";

// 29. Owner Organization - Entity reference to Organization node
create_field_storage('field_owner_organization', 'entity_reference', 1, [
  'target_type' => 'node',
]);
create_field_instance('field_owner_organization', 'billboard', 'Owner Organization', TRUE, [
  'handler' => 'default:node',
  'handler_settings' => [
    'target_bundles' => ['organization' => 'organization'],
    'auto_create' => FALSE,
  ],
  'description' => 'Billboard owner organization',
]);
echo "✓ field_owner_organization\n";

// 30. Owner/Vendor Name - Plain text (legacy field for migration)
create_field_storage('field_owner_vendor_name', 'string');
create_field_instance('field_owner_vendor_name', 'billboard', 'Owner / Vendor Name', FALSE, [
  'description' => 'Legacy field for data migration',
]);
echo "✓ field_owner_vendor_name\n";

// 31. Owner Contact Number - Plain text
create_field_storage('field_owner_contact_number', 'string');
create_field_instance('field_owner_contact_number', 'billboard', 'Owner Contact Number', FALSE);
echo "✓ field_owner_contact_number\n";

// 32. Is Premium - Boolean
create_field_storage('field_is_premium', 'boolean');
create_field_instance('field_is_premium', 'billboard', 'Is Premium', FALSE);
echo "✓ field_is_premium\n";

// 33. Is Active - Boolean
create_field_storage('field_is_active', 'boolean');
create_field_instance('field_is_active', 'billboard', 'Active', TRUE, [
  'default_value' => [['value' => 1]],
]);
echo "✓ field_is_active\n";

// 34. Hero Image - Image
create_field_storage('field_hero_image', 'image', 1, [
  'uri_scheme' => 'public',
]);
create_field_instance('field_hero_image', 'billboard', 'Hero Image', FALSE, [
  'settings' => [
    'file_directory' => 'billboards/heroes',
    'file_extensions' => 'png jpg jpeg webp',
    'max_filesize' => '5 MB',
    'max_resolution' => '4000x4000',
    'min_resolution' => '800x600',
    'alt_field' => TRUE,
    'alt_field_required' => TRUE,
    'title_field' => FALSE,
  ],
]);
echo "✓ field_hero_image\n";

// 35. Gallery - Image (multiple)
create_field_storage('field_gallery', 'image', FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED, [
  'uri_scheme' => 'public',
]);
create_field_instance('field_gallery', 'billboard', 'Gallery', FALSE, [
  'settings' => [
    'file_directory' => 'billboards/gallery',
    'file_extensions' => 'png jpg jpeg webp',
    'max_filesize' => '5 MB',
    'max_resolution' => '4000x4000',
    'alt_field' => TRUE,
    'alt_field_required' => FALSE,
    'title_field' => TRUE,
  ],
]);
echo "✓ field_gallery\n";

// 36. Notes - Long text
create_field_storage('field_notes', 'text_long');
create_field_instance('field_notes', 'billboard', 'Notes', FALSE, [
  'description' => 'Internal comments and notes',
]);
echo "✓ field_notes\n";

echo "\n✅ Billboard content type created successfully with all fields!\n";
echo "\nNext steps:\n";
echo "1. Configure form display: /admin/structure/types/manage/billboard/form-display\n";
echo "2. Configure view modes: /admin/structure/types/manage/billboard/display\n";
echo "3. Set permissions: /admin/people/permissions\n";
echo "4. Create sample billboard content\n";
