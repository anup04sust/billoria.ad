<?php

/**
 * @file
 * Configure Billboard view modes (full, teaser, card, map_marker).
 *
 * Run: ddev drush scr scripts/configure-billboard-view-modes.php
 */

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;

echo "Configuring Billboard view modes...\n\n";

// Create custom view modes if they don't exist
$view_modes = [
  'card' => 'Card',
  'map_marker' => 'Map Marker',
  'search_result' => 'Search Result',
];

foreach ($view_modes as $mode_id => $mode_label) {
  $view_mode = EntityViewMode::load('node.' . $mode_id);
  if (!$view_mode) {
    $view_mode = EntityViewMode::create([
      'id' => 'node.' . $mode_id,
      'targetEntityType' => 'node',
      'label' => $mode_label,
      'status' => TRUE,
    ]);
    $view_mode->save();
    echo "✓ Created view mode: $mode_label\n";
  }
}

echo "\n" . "Configuring display settings...\n\n";

// ===== DEFAULT (Full) View Mode =====
$display_full = EntityViewDisplay::load('node.billboard.default');
if (!$display_full) {
  $display_full = EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'billboard',
    'mode' => 'default',
    'status' => TRUE,
  ]);
}

$full_components = [
  'field_hero_image' => ['type' => 'image', 'weight' => 0, 'label' => 'hidden', 'settings' => ['image_style' => 'large', 'image_link' => '']],
  'field_billboard_id' => ['type' => 'string', 'weight' => 1, 'label' => 'inline'],
  'field_media_format' => ['type' => 'entity_reference_label', 'weight' => 2, 'label' => 'inline'],
  'field_placement_type' => ['type' => 'entity_reference_label', 'weight' => 3, 'label' => 'inline'],
  'field_display_size' => ['type' => 'string', 'weight' => 4, 'label' => 'inline'],
  'field_illumination_type' => ['type' => 'entity_reference_label', 'weight' => 5, 'label' => 'inline'],
  'field_division' => ['type' => 'entity_reference_label', 'weight' => 10, 'label' => 'inline'],
  'field_district' => ['type' => 'entity_reference_label', 'weight' => 11, 'label' => 'inline'],
  'field_area_zone' => ['type' => 'entity_reference_label', 'weight' => 12, 'label' => 'inline'],
  'field_road_name' => ['type' => 'entity_reference_label', 'weight' => 13, 'label' => 'inline'],
  'field_road_type' => ['type' => 'entity_reference_label', 'weight' => 14, 'label' => 'inline'],
  'field_latitude' => ['type' => 'number_decimal', 'weight' => 15, 'label' => 'inline'],
  'field_longitude' => ['type' => 'number_decimal', 'weight' => 16, 'label' => 'inline'],
  'field_facing_direction' => ['type' => 'list_default', 'weight' => 17, 'label' => 'inline'],
  'field_traffic_direction' => ['type' => 'entity_reference_label', 'weight' => 20, 'label' => 'inline'],
  'field_visibility_class' => ['type' => 'entity_reference_label', 'weight' => 21, 'label' => 'inline'],
  'field_rate_card_price' => ['type' => 'number_decimal', 'weight' => 30, 'label' => 'inline'],
  'field_currency' => ['type' => 'list_default', 'weight' => 31, 'label' => 'hidden'],
  'field_booking_mode' => ['type' => 'entity_reference_label', 'weight' => 32, 'label' => 'inline'],
  'field_availability_status' => ['type' => 'entity_reference_label', 'weight' => 33, 'label' => 'inline'],
  'field_owner_organization' => ['type' => 'entity_reference_label', 'weight' => 40, 'label' => 'inline', 'settings' => ['link' => TRUE]],
  'field_owner_contact_number' => ['type' => 'string', 'weight' => 41, 'label' => 'inline'],
  'field_gallery' => ['type' => 'image', 'weight' => 50, 'label' => 'above', 'settings' => ['image_style' => 'medium', 'image_link' => 'file']],
  'field_notes' => ['type' => 'text_default', 'weight' => 60, 'label' => 'above'],
];

foreach ($full_components as $field => $settings) {
  $display_full->setComponent($field, $settings);
}

// Hide fields not needed in full view
$display_full->removeComponent('field_upazila_thana');
$display_full->removeComponent('field_city_corporation');
$display_full->removeComponent('field_owner_vendor_name');
$display_full->removeComponent('field_commercial_score');
$display_full->removeComponent('field_traffic_score');
$display_full->removeComponent('field_visibility_distance');
$display_full->removeComponent('field_lane_count');
$display_full->removeComponent('field_has_divider');
$display_full->removeComponent('field_width_ft');
$display_full->removeComponent('field_height_ft');
$display_full->removeComponent('field_is_premium');
$display_full->removeComponent('field_is_active');

$display_full->save();
echo "✓ Configured default (full) view\n";

// ===== TEASER View Mode =====
$display_teaser = EntityViewDisplay::load('node.billboard.teaser');
if (!$display_teaser) {
  $display_teaser = EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'billboard',
    'mode' => 'teaser',
    'status' => TRUE,
  ]);
}

$teaser_components = [
  'field_hero_image' => ['type' => 'image', 'weight' => 0, 'label' => 'hidden', 'settings' => ['image_style' => 'medium', 'image_link' => 'content']],
  'field_display_size' => ['type' => 'string', 'weight' => 1, 'label' => 'hidden'],
  'field_area_zone' => ['type' => 'entity_reference_label', 'weight' => 2, 'label' => 'hidden'],
  'field_road_name' => ['type' => 'entity_reference_label', 'weight' => 3, 'label' => 'hidden'],
  'field_rate_card_price' => ['type' => 'number_decimal', 'weight' => 4, 'label' => 'inline'],
  'field_availability_status' => ['type' => 'entity_reference_label', 'weight' => 5, 'label' => 'hidden'],
];

foreach ($teaser_components as $field => $settings) {
  $display_teaser->setComponent($field, $settings);
}

$display_teaser->save();
echo "✓ Configured teaser view\n";

// ===== CARD View Mode =====
$display_card = EntityViewDisplay::load('node.billboard.card');
if (!$display_card) {
  $display_card = EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'billboard',
    'mode' => 'card',
    'status' => TRUE,
  ]);
}

$card_components = [
  'field_hero_image' => ['type' => 'image', 'weight' => 0, 'label' => 'hidden', 'settings' => ['image_style' => 'medium', 'image_link' => 'content']],
  'field_media_format' => ['type' => 'entity_reference_label', 'weight' => 1, 'label' => 'hidden'],
  'field_display_size' => ['type' => 'string', 'weight' => 2, 'label' => 'hidden'],
  'field_district' => ['type' => 'entity_reference_label', 'weight' => 3, 'label' => 'hidden'],
  'field_area_zone' => ['type' => 'entity_reference_label', 'weight' => 4, 'label' => 'hidden'],
  'field_rate_card_price' => ['type' => 'number_decimal', 'weight' => 5, 'label' => 'hidden'],
  'field_availability_status' => ['type' => 'entity_reference_label', 'weight' => 6, 'label' => 'hidden'],
  'field_is_premium' => ['type' => 'boolean', 'weight' => 7, 'label' => 'hidden'],
];

foreach ($card_components as $field => $settings) {
  $display_card->setComponent($field, $settings);
}

$display_card->save();
echo "✓ Configured card view\n";

// ===== MAP MARKER View Mode =====
$display_map = EntityViewDisplay::load('node.billboard.map_marker');
if (!$display_map) {
  $display_map = EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'billboard',
    'mode' => 'map_marker',
    'status' => TRUE,
  ]);
}

$map_components = [
  'field_hero_image' => ['type' => 'image', 'weight' => 0, 'label' => 'hidden', 'settings' => ['image_style' => 'thumbnail', 'image_link' => 'content']],
  'field_display_size' => ['type' => 'string', 'weight' => 1, 'label' => 'hidden'],
  'field_area_zone' => ['type' => 'entity_reference_label', 'weight' => 2, 'label' => 'hidden'],
  'field_rate_card_price' => ['type' => 'number_decimal', 'weight' => 3, 'label' => 'inline'],
  'field_availability_status' => ['type' => 'entity_reference_label', 'weight' => 4, 'label' => 'hidden'],
];

foreach ($map_components as $field => $settings) {
  $display_map->setComponent($field, $settings);
}

$display_map->save();
echo "✓ Configured map_marker view\n";

// ===== SEARCH RESULT View Mode =====
$display_search = EntityViewDisplay::load('node.billboard.search_result');
if (!$display_search) {
  $display_search = EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => 'billboard',
    'mode' => 'search_result',
    'status' => TRUE,
  ]);
}

$search_components = [
  'field_hero_image' => ['type' => 'image', 'weight' => 0, 'label' => 'hidden', 'settings' => ['image_style' => 'thumbnail', 'image_link' => 'content']],
  'field_billboard_id' => ['type' => 'string', 'weight' => 1, 'label' => 'hidden'],
  'field_media_format' => ['type' => 'entity_reference_label', 'weight' => 2, 'label' => 'inline'],
  'field_display_size' => ['type' => 'string', 'weight' => 3, 'label' => 'inline'],
  'field_division' => ['type' => 'entity_reference_label', 'weight' => 4, 'label' => 'inline'],
  'field_district' => ['type' => 'entity_reference_label', 'weight' => 5, 'label' => 'inline'],
  'field_area_zone' => ['type' => 'entity_reference_label', 'weight' => 6, 'label' => 'inline'],
  'field_road_name' => ['type' => 'entity_reference_label', 'weight' => 7, 'label' => 'inline'],
  'field_rate_card_price' => ['type' => 'number_decimal', 'weight' => 8, 'label' => 'inline'],
  'field_availability_status' => ['type' => 'entity_reference_label', 'weight' => 9, 'label' => 'inline'],
];

foreach ($search_components as $field => $settings) {
  $display_search->setComponent($field, $settings);
}

$display_search->save();
echo "✓ Configured search_result view\n";

echo "\n✅ All view modes configured successfully!\n";
echo "\nView modes created:\n";
echo "- default (full): Complete billboard information\n";
echo "- teaser: Brief preview with hero image\n";
echo "- card: Compact card display for grids\n";
echo "- map_marker: Minimal info for map popups\n";
echo "- search_result: Optimized for search listings\n";
