<?php

/**
 * @file
 * Assigns the correct district reference to each city_corporation taxonomy term.
 *
 * Usage: ddev drush php:script scripts/update-city-corporation-districts.php
 */

use Drupal\taxonomy\Entity\Term;

$storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

// City Corporation name → District name mapping.
$mapping = [
  'Dhaka North City Corporation'       => 'Dhaka',
  'Dhaka South City Corporation'       => 'Dhaka',
  'Chattogram City Corporation'        => 'Chattogram',
  'Khulna City Corporation'            => 'Khulna',
  'Rajshahi City Corporation'          => 'Rajshahi',
  'Sylhet City Corporation'            => 'Sylhet',
  'Barishal City Corporation'          => 'Barishal',
  'Gazipur City Corporation'           => 'Gazipur',
  'Cumilla City Corporation'           => 'Cumilla',
  'Rangpur City Corporation'           => 'Rangpur',
  'Mymensingh City Corporation'        => 'Mymensingh',
  'Narayanganj City Corporation'       => 'Narayanganj',
];

// Build district name → term ID lookup.
$districts = $storage->loadByProperties(['vid' => 'district', 'status' => 1]);
$district_map = [];
foreach ($districts as $district) {
  $district_map[$district->getName()] = (int) $district->id();
}

// Load all city corporation terms.
$corps = $storage->loadByProperties(['vid' => 'city_corporation', 'status' => 1]);

$updated = 0;
$skipped = 0;
$errors = 0;

echo "=== Updating City Corporation → District references ===\n\n";

foreach ($corps as $corp) {
  $name = $corp->getName();

  if (!isset($mapping[$name])) {
    echo "⚠ SKIP: '$name' — no mapping defined\n";
    $skipped++;
    continue;
  }

  $district_name = $mapping[$name];

  if (!isset($district_map[$district_name])) {
    echo "✗ ERROR: '$name' — district '$district_name' not found in taxonomy\n";
    $errors++;
    continue;
  }

  $district_id = $district_map[$district_name];

  // Check if already set correctly.
  if ($corp->hasField('field_district') && !$corp->get('field_district')->isEmpty()) {
    $current = (int) $corp->get('field_district')->target_id;
    if ($current === $district_id) {
      echo "— SKIP: '$name' — already set to '$district_name' (id:$district_id)\n";
      $skipped++;
      continue;
    }
  }

  $corp->set('field_district', $district_id);
  $corp->save();
  echo "✓ '$name' → '$district_name' (id:$district_id)\n";
  $updated++;
}

echo "\n=== Done: $updated updated, $skipped skipped, $errors errors ===\n";
