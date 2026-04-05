<?php

/**
 * @file
 * Script to import area_zone terms with relational data.
 *
 * Usage: ddev drush php:script scripts/import-area-zones.php
 */

use Drupal\taxonomy\Entity\Term;

// Area zone data refined to match existing database terms.
$area_zones = [
  // Dhaka North City Corporation
  ['name' => 'Gulshan', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'premium_urban'],
  ['name' => 'Banani', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'premium_urban'],
  ['name' => 'Baridhara', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'premium_urban'],
  ['name' => 'Mohakhali', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Tejgaon', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Tejgaon Industrial Area', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'industrial'],
  ['name' => 'Uttara Sector Belt', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'residential_mixed'],
  ['name' => 'Airport Corridor', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'airport_belt'],
  ['name' => 'Kuril', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Bashundhara Gate', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Badda', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'residential_mixed'],
  ['name' => 'Rampura Link', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'transit'],
  ['name' => 'Mirpur', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'residential_mixed'],
  ['name' => 'Pallabi', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'residential_mixed'],
  ['name' => 'Gabtoli Gateway', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka North City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'highway_gateway'],

  // Dhaka South City Corporation
  ['name' => 'Farmgate', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Shahbag', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'institutional'],
  ['name' => 'Motijheel', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Paltan', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Kakrail', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'transit'],
  ['name' => 'Dhanmondi', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Panthapath', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Green Road', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'commercial'],
  ['name' => 'New Market', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'commercial'],
  ['name' => 'Jatrabari', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'highway_gateway'],
  ['name' => 'Sayedabad', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Malibagh', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'residential_mixed'],
  ['name' => 'Hatirjheel Belt', 'district' => 'Dhaka', 'city_corporation' => 'Dhaka South City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'premium_urban'],

  // Chattogram City Corporation
  ['name' => 'Agrabad', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'GEC Circle', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Port Gate', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'port_access'],
  ['name' => 'AK Khan', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'highway_gateway'],
  ['name' => 'Oxygen', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'transit'],
  ['name' => 'New Market', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'commercial'],
  ['name' => 'Patenga', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'port_access'],
  ['name' => 'CDA Avenue Belt', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Airport Road Chattogram', 'district' => 'Chattogram', 'city_corporation' => 'Chattogram City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'airport_belt'],

  // Khulna City Corporation
  ['name' => 'Sonadanga', 'district' => 'Khulna', 'city_corporation' => 'Khulna City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Boyra', 'district' => 'Khulna', 'city_corporation' => 'Khulna City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'residential_mixed'],
  ['name' => 'Shibbari', 'district' => 'Khulna', 'city_corporation' => 'Khulna City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Daulatpur', 'district' => 'Khulna', 'city_corporation' => 'Khulna City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'industrial'],
  ['name' => 'Khan Jahan Ali Belt', 'district' => 'Khulna', 'city_corporation' => 'Khulna City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Khulna–Jessore Corridor', 'district' => 'Khulna', 'city_corporation' => 'Khulna City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'highway_gateway'],

  // Rajshahi City Corporation
  ['name' => 'Talaimari', 'district' => 'Rajshahi', 'city_corporation' => 'Rajshahi City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Laxmipur', 'district' => 'Rajshahi', 'city_corporation' => 'Rajshahi City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Railgate', 'district' => 'Rajshahi', 'city_corporation' => 'Rajshahi City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Court Area', 'district' => 'Rajshahi', 'city_corporation' => 'Rajshahi City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'institutional'],
  ['name' => 'Greater Road Belt', 'district' => 'Rajshahi', 'city_corporation' => 'Rajshahi City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Airport Road Rajshahi', 'district' => 'Rajshahi', 'city_corporation' => 'Rajshahi City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'airport_belt'],

  // Sylhet City Corporation
  ['name' => 'Amberkhana', 'district' => 'Sylhet', 'city_corporation' => 'Sylhet City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Zindabazar', 'district' => 'Sylhet', 'city_corporation' => 'Sylhet City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Humayun Rashid Chattar', 'district' => 'Sylhet', 'city_corporation' => 'Sylhet City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Airport Belt', 'district' => 'Sylhet', 'city_corporation' => 'Sylhet City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'airport_belt'],
  ['name' => 'Tamabil Road Entry', 'district' => 'Sylhet', 'city_corporation' => 'Sylhet City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'highway_gateway'],

  // Barishal City Corporation
  ['name' => 'Nathullabad', 'district' => 'Barishal', 'city_corporation' => 'Barishal City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Sadar Road', 'district' => 'Barishal', 'city_corporation' => 'Barishal City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Launch Ghat Belt', 'district' => 'Barishal', 'city_corporation' => 'Barishal City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'transit'],
  ['name' => 'Barishal Entry Corridor', 'district' => 'Barishal', 'city_corporation' => 'Barishal City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'highway_gateway'],

  // Rangpur City Corporation
  ['name' => 'Modern More', 'district' => 'Rangpur', 'city_corporation' => 'Rangpur City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Jahaj Company More', 'district' => 'Rangpur', 'city_corporation' => 'Rangpur City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Station Road', 'district' => 'Rangpur', 'city_corporation' => 'Rangpur City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'commercial'],
  ['name' => 'City Bypass Entry', 'district' => 'Rangpur', 'city_corporation' => 'Rangpur City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'highway_gateway'],

  // Mymensingh City Corporation
  ['name' => 'Charpara', 'district' => 'Mymensingh', 'city_corporation' => 'Mymensingh City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Ganginarpar', 'district' => 'Mymensingh', 'city_corporation' => 'Mymensingh City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Shambhuganj Entry', 'district' => 'Mymensingh', 'city_corporation' => 'Mymensingh City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'highway_gateway'],
  ['name' => 'Town Hall Belt', 'district' => 'Mymensingh', 'city_corporation' => 'Mymensingh City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'institutional'],

  // Gazipur City Corporation
  ['name' => 'Tongi', 'district' => 'Gazipur', 'city_corporation' => 'Gazipur City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'industrial'],
  ['name' => 'Board Bazar', 'district' => 'Gazipur', 'city_corporation' => 'Gazipur City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Gazipur Chowrasta', 'district' => 'Gazipur', 'city_corporation' => 'Gazipur City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'Konabari', 'district' => 'Gazipur', 'city_corporation' => 'Gazipur City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'industrial'],
  ['name' => 'Chandana Chowrasta', 'district' => 'Gazipur', 'city_corporation' => 'Gazipur City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],

  // Narayanganj City Corporation
  ['name' => 'Chashara', 'district' => 'Narayanganj', 'city_corporation' => 'Narayanganj City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Signboard', 'district' => 'Narayanganj', 'city_corporation' => 'Narayanganj City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'highway_gateway'],
  ['name' => 'Shimrail', 'district' => 'Narayanganj', 'city_corporation' => 'Narayanganj City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'industrial'],
  ['name' => 'Fatullah Belt', 'district' => 'Narayanganj', 'city_corporation' => 'Narayanganj City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'industrial'],

  // Cumilla City Corporation
  ['name' => 'Kandirpar', 'district' => 'Cumilla', 'city_corporation' => 'Cumilla City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'commercial'],
  ['name' => 'Tomchom Bridge Belt', 'district' => 'Cumilla', 'city_corporation' => 'Cumilla City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'transit'],
  ['name' => 'EPZ Entry Cumilla', 'district' => 'Cumilla', 'city_corporation' => 'Cumilla City Corporation', 'priority_tier' => 'tier_2', 'zone_type' => 'industrial'],
  ['name' => 'Dhaka–Chattogram Highway Entry', 'district' => 'Cumilla', 'city_corporation' => 'Cumilla City Corporation', 'priority_tier' => 'tier_1', 'zone_type' => 'highway_gateway'],
];

// Load taxonomy terms for reference mapping.
$district_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'district']);

$district_map = [];
foreach ($district_terms as $term) {
  $district_map[$term->getName()] = $term->id();
}

$city_corp_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'city_corporation']);

$city_corp_map = [];
foreach ($city_corp_terms as $term) {
  $city_corp_map[$term->getName()] = $term->id();
}

$created = 0;
$existing = 0;
$failed = 0;

echo "Importing Area/Zone terms with relational data...\n\n";

foreach ($area_zones as $data) {
  // Check if term exists.
  $existing_terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'vid' => 'area_zone',
      'name' => $data['name'],
    ]);

  if (!empty($existing_terms)) {
    $existing++;
    continue;
  }

  // Get references.
  $district_tid = $district_map[$data['district']] ?? NULL;
  $city_corp_tid = isset($data['city_corporation']) ? ($city_corp_map[$data['city_corporation']] ?? NULL) : NULL;

  if (!$district_tid) {
    echo "✗ District not found: {$data['district']} for {$data['name']}\n";
    $failed++;
    continue;
  }

  if (isset($data['city_corporation']) && !$city_corp_tid) {
    echo "✗ City Corporation not found: {$data['city_corporation']} for {$data['name']}\n";
    $failed++;
    continue;
  }

  // Create term.
  $term_data = [
    'vid' => 'area_zone',
    'name' => $data['name'],
    'field_district' => ['target_id' => $district_tid],
    'field_priority_tier' => $data['priority_tier'],
    'field_zone_type' => $data['zone_type'],
    'field_is_active' => TRUE,
  ];

  if ($city_corp_tid) {
    $term_data['field_city_corporation'] = ['target_id' => $city_corp_tid];
  }

  $term = Term::create($term_data);

  try {
    $term->save();
    $created++;
    $city_corp_label = $data['city_corporation'] ?? 'N/A';
    echo "✓ Created: {$data['name']} [{$data['district']}] - {$data['zone_type']}\n";
  }
  catch (\Exception $e) {
    $failed++;
    echo "✗ Failed: {$data['name']} - {$e->getMessage()}\n";
  }
}

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "AREA ZONE IMPORT SUMMARY:\n";
echo "Created: $created terms\n";
echo "Already existed: $existing terms\n";
echo "Failed: $failed terms\n";
echo "\n✓ Area zone import complete!\n";
