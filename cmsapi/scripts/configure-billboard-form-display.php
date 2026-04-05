<?php

/**
 * @file
 * Configure Billboard content type form display.
 *
 * Run: ddev drush scr scripts/configure-billboard-form-display.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;

echo "Configuring Billboard form display...\n\n";

$form_display = EntityFormDisplay::load('node.billboard.default');

if (!$form_display) {
  $form_display = EntityFormDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'billboard',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

// Configure field weights and widgets
$components = [
  // Basic Information (weight 0-10)
  'title' => [
    'type' => 'string_textfield',
    'weight' => 0,
    'region' => 'content',
    'settings' => ['size' => 60, 'placeholder' => 'e.g., Airport Road Billboard - Near Jasimuddin'],
  ],
  'field_billboard_id' => [
    'type' => 'string_textfield',
    'weight' => 1,
    'region' => 'content',
    'settings' => ['size' => 30, 'placeholder' => 'Auto-generated or custom'],
  ],

  // Media & Display (weight 10-20)
  'field_media_format' => [
    'type' => 'options_select',
    'weight' => 10,
    'region' => 'content',
  ],
  'field_placement_type' => [
    'type' => 'options_select',
    'weight' => 11,
    'region' => 'content',
  ],
  'field_display_size' => [
    'type' => 'string_textfield',
    'weight' => 12,
    'region' => 'content',
    'settings' => ['size' => 30, 'placeholder' => 'e.g., 20x30 ft'],
  ],
  'field_width_ft' => [
    'type' => 'number',
    'weight' => 13,
    'region' => 'content',
    'settings' => ['placeholder' => '20'],
  ],
  'field_height_ft' => [
    'type' => 'number',
    'weight' => 14,
    'region' => 'content',
    'settings' => ['placeholder' => '30'],
  ],
  'field_illumination_type' => [
    'type' => 'options_select',
    'weight' => 15,
    'region' => 'content',
  ],

  // Location (weight 20-35)
  'field_division' => [
    'type' => 'options_select',
    'weight' => 20,
    'region' => 'content',
  ],
  'field_district' => [
    'type' => 'options_select',
    'weight' => 21,
    'region' => 'content',
  ],
  'field_upazila_thana' => [
    'type' => 'options_select',
    'weight' => 22,
    'region' => 'content',
  ],
  'field_city_corporation' => [
    'type' => 'options_select',
    'weight' => 23,
    'region' => 'content',
  ],
  'field_area_zone' => [
    'type' => 'options_select',
    'weight' => 24,
    'region' => 'content',
  ],
  'field_road_name' => [
    'type' => 'options_select',
    'weight' => 25,
    'region' => 'content',
  ],
  'field_road_type' => [
    'type' => 'options_select',
    'weight' => 26,
    'region' => 'content',
  ],
  'field_latitude' => [
    'type' => 'number',
    'weight' => 27,
    'region' => 'content',
    'settings' => ['placeholder' => '23.8103'],
  ],
  'field_longitude' => [
    'type' => 'number',
    'weight' => 28,
    'region' => 'content',
    'settings' => ['placeholder' => '90.4125'],
  ],
  'field_facing_direction' => [
    'type' => 'options_select',
    'weight' => 29,
    'region' => 'content',
  ],

  // Traffic & Visibility (weight 35-45)
  'field_traffic_direction' => [
    'type' => 'options_select',
    'weight' => 35,
    'region' => 'content',
  ],
  'field_visibility_class' => [
    'type' => 'options_select',
    'weight' => 36,
    'region' => 'content',
  ],
  'field_visibility_distance' => [
    'type' => 'number',
    'weight' => 37,
    'region' => 'content',
    'settings' => ['placeholder' => '100'],
  ],
  'field_lane_count' => [
    'type' => 'number',
    'weight' => 38,
    'region' => 'content',
    'settings' => ['placeholder' => '4'],
  ],
  'field_has_divider' => [
    'type' => 'boolean_checkbox',
    'weight' => 39,
    'region' => 'content',
    'settings' => ['display_label' => TRUE],
  ],

  // Commercial & Pricing (weight 45-55)
  'field_rate_card_price' => [
    'type' => 'number',
    'weight' => 45,
    'region' => 'content',
    'settings' => ['placeholder' => '50000'],
  ],
  'field_currency' => [
    'type' => 'options_select',
    'weight' => 46,
    'region' => 'content',
  ],
  'field_commercial_score' => [
    'type' => 'number',
    'weight' => 47,
    'region' => 'content',
    'settings' => ['placeholder' => '1-100', 'min' => 1, 'max' => 100],
  ],
  'field_traffic_score' => [
    'type' => 'number',
    'weight' => 48,
    'region' => 'content',
    'settings' => ['placeholder' => '1-100', 'min' => 1, 'max' => 100],
  ],

  // Booking (weight 55-65)
  'field_booking_mode' => [
    'type' => 'options_select',
    'weight' => 55,
    'region' => 'content',
  ],
  'field_availability_status' => [
    'type' => 'options_select',
    'weight' => 56,
    'region' => 'content',
  ],

  // Ownership (weight 65-75)
  'field_owner_organization' => [
    'type' => 'entity_reference_autocomplete',
    'weight' => 65,
    'region' => 'content',
    'settings' => ['match_operator' => 'CONTAINS', 'size' => 60, 'placeholder' => 'Start typing organization name...'],
  ],
  'field_owner_vendor_name' => [
    'type' => 'string_textfield',
    'weight' => 66,
    'region' => 'content',
    'settings' => ['size' => 60, 'placeholder' => 'Legacy field - use Owner Organization instead'],
  ],
  'field_owner_contact_number' => [
    'type' => 'string_textfield',
    'weight' => 67,
    'region' => 'content',
    'settings' => ['size' => 30, 'placeholder' => '+8801712345678'],
  ],

  // Images (weight 75-85)
  'field_hero_image' => [
    'type' => 'image_image',
    'weight' => 75,
    'region' => 'content',
    'settings' => ['progress_indicator' => 'throbber', 'preview_image_style' => 'thumbnail'],
  ],
  'field_gallery' => [
    'type' => 'image_image',
    'weight' => 76,
    'region' => 'content',
    'settings' => ['progress_indicator' => 'throbber', 'preview_image_style' => 'thumbnail'],
  ],

  // Status & Flags (weight 85-95)
  'field_is_premium' => [
    'type' => 'boolean_checkbox',
    'weight' => 85,
    'region' => 'content',
    'settings' => ['display_label' => TRUE],
  ],
  'field_is_active' => [
    'type' => 'boolean_checkbox',
    'weight' => 86,
    'region' => 'content',
    'settings' => ['display_label' => TRUE],
  ],

  // Notes (weight 95-100)
  'field_notes' => [
    'type' => 'text_textarea',
    'weight' => 95,
    'region' => 'content',
    'settings' => ['rows' => 5, 'placeholder' => 'Internal notes and comments...'],
  ],
];

foreach ($components as $field_name => $config) {
  $form_display->setComponent($field_name, $config);
  echo "✓ Configured $field_name\n";
}

// Hide fields not needed in form
$form_display->removeComponent('uid');
$form_display->removeComponent('created');
$form_display->removeComponent('promote');
$form_display->removeComponent('sticky');

$form_display->save();

echo "\n✅ Billboard form display configured successfully!\n";
echo "\nForm organization:\n";
echo "- Basic Information (0-10)\n";
echo "- Media & Display (10-20)\n";
echo "- Location (20-35)\n";
echo "- Traffic & Visibility (35-45)\n";
echo "- Commercial & Pricing (45-55)\n";
echo "- Booking (55-65)\n";
echo "- Ownership (65-75)\n";
echo "- Images (75-85)\n";
echo "- Status & Flags (85-95)\n";
echo "- Notes (95-100)\n";
