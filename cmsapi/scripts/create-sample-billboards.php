<?php

/**
 * @file
 * Create sample billboard content for testing.
 *
 * Run: ddev drush scr scripts/create-sample-billboards.php
 */

use Drupal\node\Entity\Node;

echo "Creating sample billboard content...\n\n";

// First, get some taxonomy term IDs
$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

// Get division (Dhaka)
$dhaka_division = $term_storage->loadByProperties([
  'vid' => 'division',
  'name' => 'Dhaka',
]);
$dhaka_division_tid = $dhaka_division ? reset($dhaka_division)->id() : NULL;

// Get district (Dhaka)
$dhaka_district = $term_storage->loadByProperties([
  'vid' => 'district',
  'name' => 'Dhaka',
]);
$dhaka_district_tid = $dhaka_district ? reset($dhaka_district)->id() : NULL;

// Get area zone (Gulshan)
$gulshan_zone = $term_storage->loadByProperties([
  'vid' => 'area_zone',
  'name' => 'Gulshan',
]);
$gulshan_zone_tid = $gulshan_zone ? reset($gulshan_zone)->id() : NULL;

// Get media format
$media_format = $term_storage->loadByProperties([
  'vid' => 'media_format',
]);
$media_format_tid = $media_format ? reset($media_format)->id() : NULL;

// Get placement type
$placement_type = $term_storage->loadByProperties([
  'vid' => 'placement_type',
]);
$placement_type_tid = $placement_type ? reset($placement_type)->id() : NULL;

// Get availability status (Available)
$availability = $term_storage->loadByProperties([
  'vid' => 'availability_status',
  'name' => 'Available',
]);
$availability_tid = $availability ? reset($availability)->id() : NULL;

// Get booking mode
$booking_mode = $term_storage->loadByProperties([
  'vid' => 'booking_mode',
]);
$booking_mode_tid = $booking_mode ? reset($booking_mode)->id() : NULL;

// Get an owner organization (first organization node)
$org_storage = \Drupal::entityTypeManager()->getStorage('node');
$org_query = $org_storage->getQuery()
  ->condition('type', 'organization')
  ->accessCheck(FALSE)
  ->range(0, 1);
$org_nids = $org_query->execute();
$org_nid = $org_nids ? reset($org_nids) : NULL;

if (!$org_nid) {
  echo "⚠️  No organization found. Please create an organization first.\n";
  echo "You can create one via: ddev drush php-eval \"\\\$org = \\\\Drupal\\\\node\\\\Entity\\\\Node::create(['type' => 'organization', 'title' => 'Test Billboard Owner', 'field_org_type' => 'owner']); \\\$org->save();\"\n";
  exit(1);
}

// Sample billboard data
$billboards = [
  [
    'title' => 'Airport Road Premium Billboard - Near Jasimuddin',
    'field_billboard_id' => 'BB-DH-001',
    'field_display_size' => '20x30 ft',
    'field_width_ft' => 20,
    'field_height_ft' => 30,
    'field_latitude' => 23.8103,
    'field_longitude' => 90.4125,
    'field_rate_card_price' => 150000,
    'field_commercial_score' => 85,
    'field_traffic_score' => 90,
    'field_is_premium' => TRUE,
    'field_is_active' => TRUE,
    'field_facing_direction' => 'north',
    'field_lane_count' => 6,
    'field_has_divider' => TRUE,
    'field_visibility_distance' => 200,
  ],
  [
    'title' => 'Gulshan Avenue Billboard - Circle 1',
    'field_billboard_id' => 'BB-DH-002',
    'field_display_size' => '15x25 ft',
    'field_width_ft' => 15,
    'field_height_ft' => 25,
    'field_latitude' => 23.7809,
    'field_longitude' => 90.4132,
    'field_rate_card_price' => 120000,
    'field_commercial_score' => 90,
    'field_traffic_score' => 85,
    'field_is_premium' => TRUE,
    'field_is_active' => TRUE,
    'field_facing_direction' => 'south',
    'field_lane_count' => 4,
    'field_has_divider' => TRUE,
    'field_visibility_distance' => 150,
  ],
  [
    'title' => 'Mirpur Road Digital Billboard',
    'field_billboard_id' => 'BB-DH-003',
    'field_display_size' => '12x18 ft',
    'field_width_ft' => 12,
    'field_height_ft' => 18,
    'field_latitude' => 23.7693,
    'field_longitude' => 90.3688,
    'field_rate_card_price' => 80000,
    'field_commercial_score' => 75,
    'field_traffic_score' => 80,
    'field_is_premium' => FALSE,
    'field_is_active' => TRUE,
    'field_facing_direction' => 'east',
    'field_lane_count' => 4,
    'field_has_divider' => FALSE,
    'field_visibility_distance' => 120,
  ],
  [
    'title' => 'Banani Overpass Billboard',
    'field_billboard_id' => 'BB-DH-004',
    'field_display_size' => '18x25 ft',
    'field_width_ft' => 18,
    'field_height_ft' => 25,
    'field_latitude' => 23.7937,
    'field_longitude' => 90.4066,
    'field_rate_card_price' => 110000,
    'field_commercial_score' => 82,
    'field_traffic_score' => 88,
    'field_is_premium' => TRUE,
    'field_is_active' => TRUE,
    'field_facing_direction' => 'west',
    'field_lane_count' => 6,
    'field_has_divider' => TRUE,
    'field_visibility_distance' => 180,
  ],
  [
    'title' => 'Dhanmondi 27 Road Billboard',
    'field_billboard_id' => 'BB-DH-005',
    'field_display_size' => '10x15 ft',
    'field_width_ft' => 10,
    'field_height_ft' => 15,
    'field_latitude' => 23.7461,
    'field_longitude' => 90.3742,
    'field_rate_card_price' => 60000,
    'field_commercial_score' => 70,
    'field_traffic_score' => 65,
    'field_is_premium' => FALSE,
    'field_is_active' => TRUE,
    'field_facing_direction' => 'north_east',
    'field_lane_count' => 2,
    'field_has_divider' => FALSE,
    'field_visibility_distance' => 100,
  ],
];

$created_count = 0;
foreach ($billboards as $data) {
  // Add common fields
  $data['type'] = 'billboard';
  $data['status'] = 1;
  $data['field_owner_organization'] = $org_nid;
  $data['field_currency'] = 'BDT';

  // Add taxonomy references
  if ($dhaka_division_tid) $data['field_division'] = $dhaka_division_tid;
  if ($dhaka_district_tid) $data['field_district'] = $dhaka_district_tid;
  if ($gulshan_zone_tid) $data['field_area_zone'] = $gulshan_zone_tid;
  if ($media_format_tid) $data['field_media_format'] = $media_format_tid;
  if ($placement_type_tid) $data['field_placement_type'] = $placement_type_tid;
  if ($availability_tid) $data['field_availability_status'] = $availability_tid;
  if ($booking_mode_tid) $data['field_booking_mode'] = $booking_mode_tid;

  $billboard = Node::create($data);
  $billboard->save();

  echo "✓ Created: {$data['title']} (nid: {$billboard->id()})\n";
  $created_count++;
}

echo "\n✅ Created $created_count sample billboards successfully!\n";
echo "\nYou can view them at:\n";
echo "- Admin: https://billoria.ad.ddev.site/admin/content?type=billboard\n";
echo "- API: https://billoria.ad.ddev.site/api/v1/billboard/list\n";
