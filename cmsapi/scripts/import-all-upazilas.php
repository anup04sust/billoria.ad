<?php

/**
 * Import all 495 upazilas from CSV with duplicate name handling
 * Uses unique naming format: "Upazila (District)" for duplicates
 */

use Drupal\taxonomy\Entity\Term;

// Bootstrap Drupal
require_once __DIR__ . '/../vendor/autoload.php';
$kernel = \Drupal\Core\DrupalKernel::createFromRequest(
  \Symfony\Component\HttpFoundation\Request::createFromGlobals(),
  $autoloader = require __DIR__ . '/../vendor/autoload.php',
  'prod'
);
$kernel->boot();
$container = $kernel->getContainer();
$container->get('current_user')->setAccount(new \Drupal\Core\Session\UserSession(['uid' => 1]));

echo "=== IMPORTING ALL UPAZILAS/THANAS FROM CSV ===\n\n";

// Read CSV file
$csv_file = '/var/www/html/bangladesh_sub_district_upazila_master.csv';
$csv_data = array_map('str_getcsv', file($csv_file));
array_shift($csv_data); // Remove header

// Known duplicate upazila names (from analysis)
$duplicate_names = [
  'Nawabganj', 'Kaliganj', 'Sreepur', 'Daulatpur', 'Kachua',
  'Lohagara', 'Companiganj', 'Kawkhali', 'Shibganj', 'Durgapur',
  'Phulbari', 'Pirganj'
];

// Get all districts with mapping
$districts = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'district']);

$district_map = [];
$district_variations = [];
foreach ($districts as $district) {
  $name = trim($district->getName());
  $district_map[$name] = $district->id();
  $district_variations[strtolower($name)] = $name;

  // Add spelling variations
  if ($name === 'Jhalokati') {
    $district_variations['jhalokathi'] = $name; // CSV uses "Jhalokathi"
  }
  if ($name === 'Jessore') {
    $district_variations['jashore'] = $name;
  }
}

// Get existing terms to avoid true duplicates
$existing_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'upazila_thana']);

$existing_map = [];
foreach ($existing_terms as $term) {
  $name = $term->getName();
  $district_id = $term->hasField('field_district') && !$term->get('field_district')->isEmpty()
    ? $term->get('field_district')->target_id : null;
  $key = strtolower($name) . '_' . $district_id;
  $existing_map[$key] = $term->id();
}

// Import counters
$created = 0;
$skipped = 0;
$failed = 0;
$district_not_found = [];

echo "Processing 495 upazila records...\n";
echo "============================================================\n\n";

foreach ($csv_data as $row) {
  $csv_id = $row[0];
  $name = trim($row[1]);
  $machine_name = trim($row[2]);
  $csv_district = trim($row[3]);
  $csv_division = trim($row[4]);
  $is_metro = strtolower(trim($row[5])) === 'yes';

  // Map district name (handle spelling variations)
  $csv_district_lower = strtolower($csv_district);
  if (!isset($district_variations[$csv_district_lower])) {
    if (!in_array($csv_district, $district_not_found)) {
      $district_not_found[] = $csv_district;
    }
    $failed++;
    continue;
  }

  $actual_district_name = $district_variations[$csv_district_lower];
  $district_id = $district_map[$actual_district_name];

  // Create unique name for duplicates
  $display_name = in_array($name, $duplicate_names)
    ? "$name ($actual_district_name)"
    : $name;

  // Check if this exact term already exists (by name and district)
  $check_key = strtolower($display_name) . '_' . $district_id;
  if (isset($existing_map[$check_key])) {
    $skipped++;
    continue;
  }

  // Determine type (thana for metro areas, upazila for others)
  $type = $is_metro ? 'thana' : 'upazila';

  // Create geo code (e.g., "DH-DH-01" for Dhaka division, Dhaka district)
  $div_code = strtoupper(substr($csv_division, 0, 2));
  $dist_code = strtoupper(substr($actual_district_name, 0, 2));
  $geo_code = sprintf("%s-%s-%03d", $div_code, $dist_code, (int)$csv_id);

  try {
    $term = Term::create([
      'vid' => 'upazila_thana',
      'name' => $display_name,
      'field_district' => ['target_id' => $district_id],
      'field_geo_code' => $geo_code,
      'field_type' => $type,
    ]);
    $term->save();
    $created++;

    // Show progress every 50 terms
    if ($created % 50 === 0) {
      echo "  Imported $created terms...\n";
    }
  }
  catch (\Exception $e) {
    echo "❌ Failed to create '$display_name': " . $e->getMessage() . "\n";
    $failed++;
  }
}

echo "\n============================================================\n";
echo "=== IMPORT COMPLETE ===\n\n";
echo "✅ Created: $created\n";
echo "⏭️  Skipped (existing): $skipped\n";
echo "❌ Failed: $failed\n";

if (count($district_not_found) > 0) {
  echo "\n⚠️  Districts not found in database:\n";
  foreach ($district_not_found as $dist) {
    echo "  - $dist\n";
  }
}

echo "\n✓ Import process completed\n";
