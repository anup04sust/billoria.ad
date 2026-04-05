<?php

/**
 * @file
 * Create 150 test billboards across Bangladesh for map testing.
 *
 * Run: ddev drush scr scripts/create-bulk-test-billboards.php
 */

use Drupal\node\Entity\Node;

$count = 150; // Number of billboards to create

echo "Creating $count test billboards across Bangladesh...\n\n";

// First, get taxonomy term IDs
$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

// Get divisions
$divisions = $term_storage->loadByProperties(['vid' => 'division']);
$division_ids = array_map(fn($t) => $t->id(), $divisions);

// Get districts
$districts = $term_storage->loadByProperties(['vid' => 'district']);
$district_ids = array_map(fn($t) => $t->id(), $districts);

// Get area zones
$area_zones = $term_storage->loadByProperties(['vid' => 'area_zone']);
$area_zone_ids = array_map(fn($t) => $t->id(), $area_zones);

// Get media formats
$media_formats = $term_storage->loadByProperties(['vid' => 'media_format']);
$media_format_ids = array_map(fn($t) => $t->id(), $media_formats);

// Get placement types
$placement_types = $term_storage->loadByProperties(['vid' => 'placement_type']);
$placement_type_ids = array_map(fn($t) => $t->id(), $placement_types);

// Get availability status
$availability_statuses = $term_storage->loadByProperties(['vid' => 'availability_status']);
$availability_ids = array_map(fn($t) => $t->id(), $availability_statuses);

// Get booking modes
$booking_modes = $term_storage->loadByProperties(['vid' => 'booking_mode']);
$booking_mode_ids = array_map(fn($t) => $t->id(), $booking_modes);

// Get organization
$org_storage = \Drupal::entityTypeManager()->getStorage('node');
$org_query = $org_storage->getQuery()
  ->condition('type', 'organization')
  ->accessCheck(FALSE)
  ->range(0, 1);
$org_nids = $org_query->execute();
$org_nid = $org_nids ? reset($org_nids) : NULL;

if (!$org_nid) {
  echo "⚠️  No organization found. Creating default organization...\n";
  $org = Node::create([
    'type' => 'organization',
    'title' => 'Test Billboard Owner Co.',
    'status' => 1,
  ]);
  $org->save();
  $org_nid = $org->id();
  echo "✓ Created organization (nid: $org_nid)\n\n";
}

// Major cities in Bangladesh with their coordinates
$cities = [
  ['name' => 'Dhaka', 'lat' => 23.8103, 'lng' => 90.4125],
  ['name' => 'Chittagong', 'lat' => 22.3569, 'lng' => 91.7832],
  ['name' => 'Sylhet', 'lat' => 24.8949, 'lng' => 91.8687],
  ['name' => 'Rajshahi', 'lat' => 24.3745, 'lng' => 88.6042],
  ['name' => 'Khulna', 'lat' => 22.8456, 'lng' => 89.5403],
  ['name' => 'Barisal', 'lat' => 22.7010, 'lng' => 90.3535],
  ['name' => 'Rangpur', 'lat' => 25.7439, 'lng' => 89.2752],
  ['name' => 'Mymensingh', 'lat' => 24.7471, 'lng' => 90.4203],
  ['name' => 'Comilla', 'lat' => 23.4607, 'lng' => 91.1809],
  ['name' => 'Narayanganj', 'lat' => 23.6238, 'lng' => 90.5000],
  ['name' => 'Gazipur', 'lat' => 23.9999, 'lng' => 90.4203],
  ['name' => 'Cox\'s Bazar', 'lat' => 21.4272, 'lng' => 92.0058],
];

$locations = [
  'Main Road', 'Highway Junction', 'City Center', 'Shopping District',
  'Airport Road', 'Ring Road', 'Overpass', 'Underpass', 'Bridge Side',
  'Market Area', 'Station Road', 'Avenue', 'Circle', 'Crossing',
];

$facing_directions = ['north', 'south', 'east', 'west', 'north_east', 'north_west', 'south_east', 'south_west'];

$sizes = [
  ['size' => '10x15 ft', 'w' => 10, 'h' => 15, 'price' => 50000],
  ['size' => '12x18 ft', 'w' => 12, 'h' => 18, 'price' => 75000],
  ['size' => '15x20 ft', 'w' => 15, 'h' => 20, 'price' => 100000],
  ['size' => '15x25 ft', 'w' => 15, 'h' => 25, 'price' => 120000],
  ['size' => '18x25 ft', 'w' => 18, 'h' => 25, 'price' => 140000],
  ['size' => '20x30 ft', 'w' => 20, 'h' => 30, 'price' => 180000],
  ['size' => '25x40 ft', 'w' => 25, 'h' => 40, 'price' => 250000],
];

$created_count = 0;
$start_id = 1000; // Start from BB-1000

for ($i = 0; $i < $count; $i++) {
  // Random city
  $city = $cities[array_rand($cities)];

  // Random location name
  $location = $locations[array_rand($locations)];

  // Random coordinates near the city (within ~10km radius)
  $lat_offset = (mt_rand(-100, 100) / 1000); // ±0.1 degrees (~10km)
  $lng_offset = (mt_rand(-100, 100) / 1000);
  $latitude = $city['lat'] + $lat_offset;
  $longitude = $city['lng'] + $lng_offset;

  // Random size
  $size = $sizes[array_rand($sizes)];

  // Random price variation (±20%)
  $base_price = $size['price'];
  $price_variation = mt_rand(80, 120) / 100;
  $price = round($base_price * $price_variation, -3); // Round to nearest 1000

  // Billboard data
  $billboard_id = sprintf('BB-TEST-%04d', $start_id + $i);

  $data = [
    'type' => 'billboard',
    'title' => "{$city['name']} - {$location} Billboard #{$i}",
    'status' => 1,
    'field_billboard_id' => $billboard_id,
    'field_display_size' => $size['size'],
    'field_width_ft' => $size['w'],
    'field_height_ft' => $size['h'],
    'field_latitude' => $latitude,
    'field_longitude' => $longitude,
    'field_rate_card_price' => $price,
    'field_currency' => 'BDT',
    'field_commercial_score' => mt_rand(60, 95),
    'field_traffic_score' => mt_rand(60, 95),
    'field_is_premium' => (mt_rand(1, 100) > 70), // 30% premium
    'field_is_active' => TRUE,
    'field_facing_direction' => $facing_directions[array_rand($facing_directions)],
    'field_lane_count' => mt_rand(2, 8),
    'field_has_divider' => (mt_rand(0, 1) === 1),
    'field_visibility_distance' => mt_rand(80, 250),
    'field_owner_organization' => $org_nid,
  ];

  // Add random taxonomy terms
  if (!empty($division_ids)) {
    $data['field_division'] = $division_ids[array_rand($division_ids)];
  }
  if (!empty($district_ids)) {
    $data['field_district'] = $district_ids[array_rand($district_ids)];
  }
  if (!empty($area_zone_ids)) {
    $data['field_area_zone'] = $area_zone_ids[array_rand($area_zone_ids)];
  }
  if (!empty($media_format_ids)) {
    $data['field_media_format'] = $media_format_ids[array_rand($media_format_ids)];
  }
  if (!empty($placement_type_ids)) {
    $data['field_placement_type'] = $placement_type_ids[array_rand($placement_type_ids)];
  }
  if (!empty($availability_ids)) {
    $data['field_availability_status'] = $availability_ids[array_rand($availability_ids)];
  }
  if (!empty($booking_mode_ids)) {
    $data['field_booking_mode'] = $booking_mode_ids[array_rand($booking_mode_ids)];
  }

  $billboard = Node::create($data);
  $billboard->save();

  $created_count++;

  // Progress indicator
  if ($created_count % 10 === 0) {
    echo "✓ Created $created_count billboards...\n";
  }
}

echo "\n✅ Successfully created $created_count test billboards!\n";
echo "\nDistribution across Bangladesh:\n";
foreach ($cities as $city) {
  echo "  - {$city['name']}\n";
}
echo "\nView them at:\n";
echo "- Admin: https://billoria-ad-api.ddev.site/admin/content?type=billboard\n";
echo "- API: https://billoria-ad-api.ddev.site/api/v1/billboard/list\n";
echo "- Map: Your frontend map component\n";
