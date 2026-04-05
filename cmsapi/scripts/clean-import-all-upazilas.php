<?php

/**
 * Clean import of all 495 official upazilas from government CSV
 * Step 1: Delete existing upazila_thana terms
 * Step 2: Import all from CSV with duplicate name handling
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

echo "=== CLEAN IMPORT: ALL BANGLADESH UPAZILAS/THANAS ===\n\n";

// Import from CSV (existing terms already deleted via SQL)
echo "Importing all 495 upazilas from official CSV...\n";
echo "============================================================\n\n";

// Read CSV file
$csv_file = '/var/www/html/bangladesh_sub_district_upazila_master.csv';
$csv_data = array_map('str_getcsv', file($csv_file));
array_shift($csv_data); // Remove header

// Known duplicate upazila names that need district suffix
$duplicate_names = [
  'Nawabganj', 'Kaliganj', 'Sreepur', 'Daulatpur', 'Kachua',
  'Lohagara', 'Companiganj', 'Kawkhali', 'Shibganj', 'Durgapur',
  'Phulbari', 'Pirganj', 'Mirpur', 'Mohammadpur'
];

// Get all districts with name variations mapping
$districts = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'district']);

$district_map = [];
$district_variations = [];
foreach ($districts as $district) {
  $name = trim($district->getName());
  $district_map[$name] = $district->id();
  $district_variations[strtolower($name)] = $name;

  // Add known spelling variations
  if ($name === 'Jhalokati') {
    $district_variations['jhalokathi'] = $name; // CSV uses "Jhalokathi"
  }
  if ($name === 'Jessore') {
    $district_variations['jashore'] = $name;
  }
}

// Import counters
$created = 0;
$failed = 0;
$district_not_found = [];

foreach ($csv_data as $index => $row) {
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
      echo "⚠️  District not found: $csv_district (upazila: $name)\n";
    }
    $failed++;
    continue;
  }

  $actual_district_name = $district_variations[$csv_district_lower];
  $district_id = $district_map[$actual_district_name];

  // Create unique name for known duplicates
  $display_name = in_array($name, $duplicate_names)
    ? "$name ($actual_district_name)"
    : $name;

  // Determine type (thana for metro areas, upazila for others)
  $type = $is_metro ? 'thana' : 'upazila';

  // Create geo code (Division-District-ID format)
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
      echo "  Created $created terms...\n";
    }
  }
  catch (\Exception $e) {
    echo "❌ Failed to create '$display_name': " . $e->getMessage() . "\n";
    $failed++;
  }
}

echo "\n============================================================\n";
echo "=== IMPORT COMPLETE ===\n\n";
echo "✅ Successfully created: $created\n";
echo "❌ Failed: $failed\n";

if (count($district_not_found) > 0) {
  echo "\n⚠️  Districts not found in database (" . count($district_not_found) . "):\n";
  foreach ($district_not_found as $dist) {
    echo "  - $dist\n";
  }
}

// Show breakdown by district
echo "\n=== VERIFICATION ===\n";
echo "Counting terms by district...\n\n";

$query = \Drupal::database()->select('taxonomy_term_field_data', 't');
$query->fields('d', ['name']);
$query->addExpression('COUNT(t.tid)', 'count');
$query->leftJoin('taxonomy_term__field_district', 'fd', 't.tid = fd.entity_id');
$query->leftJoin('taxonomy_term_field_data', 'd', 'fd.field_district_target_id = d.tid');
$query->condition('t.vid', 'upazila_thana');
$query->groupBy('d.name');
$query->orderBy('count', 'DESC');
$results = $query->execute()->fetchAll();

$top_10 = array_slice($results, 0, 10);
foreach ($top_10 as $row) {
  echo "  {$row->name}: {$row->count} upazilas\n";
}

echo "\n✓ Clean import completed successfully\n";
