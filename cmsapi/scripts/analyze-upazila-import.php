<?php

/**
 * Analysis script for upazila/thana CSV import
 * Checks for duplicates and verifies district mappings
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

echo "=== UPAZILA/THANA CSV IMPORT ANALYSIS ===\n\n";

// Read CSV file (use path inside DDEV container)
$csv_file = '/var/www/html/bangladesh_sub_district_upazila_master.csv';
if (!file_exists($csv_file)) {
  die("ERROR: CSV file not found at $csv_file\n");
}

$csv_data = array_map('str_getcsv', file($csv_file));
$headers = array_shift($csv_data); // Remove header row
echo "CSV file contains: " . count($csv_data) . " upazila/thana records\n\n";

// Get existing upazila_thana terms
$existing_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'upazila_thana']);

$existing_names = [];
$existing_machine_names = [];
foreach ($existing_terms as $term) {
  $name = strtolower(trim($term->getName()));
  $existing_names[$name] = [
    'tid' => $term->id(),
    'name' => $term->getName(),
    'district' => $term->hasField('field_district') && !$term->get('field_district')->isEmpty()
      ? $term->get('field_district')->entity->getName() : 'N/A',
  ];
}

echo "Current database has: " . count($existing_names) . " upazila/thana terms\n";
echo "------------------------------------------------------------\n\n";

// Get all districts for mapping
$districts = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'district']);

$district_map = [];
$district_name_variations = [];
foreach ($districts as $district) {
  $district_name = trim($district->getName());
  $district_map[$district_name] = $district->id();
  $district_name_lower = strtolower($district_name);
  $district_name_variations[$district_name_lower] = $district_name;

  // Add common variations
  if ($district_name === 'Jessore') {
    $district_name_variations['jashore'] = $district_name;
  }
  if ($district_name === 'Cox\'s Bazar') {
    $district_name_variations['cox\'s bazar'] = $district_name;
    $district_name_variations['coxs bazar'] = $district_name;
  }
}

echo "Districts in database: " . count($district_map) . "\n\n";

// Analysis arrays
$duplicates = [];
$new_terms = [];
$district_mismatches = [];
$unmapped_districts = [];

// Analyze each CSV row
foreach ($csv_data as $index => $row) {
  $csv_term_id = $row[0];
  $name = trim($row[1]);
  $machine_name = trim($row[2]);
  $csv_district = trim($row[3]);
  $csv_division = trim($row[4]);
  $is_metro = trim($row[5]);
  $is_active = trim($row[6]);

  $name_lower = strtolower($name);

  // Check for duplicates (by name)
  if (isset($existing_names[$name_lower])) {
    $duplicates[] = [
      'csv_name' => $name,
      'csv_district' => $csv_district,
      'db_name' => $existing_names[$name_lower]['name'],
      'db_district' => $existing_names[$name_lower]['district'],
      'match' => ($csv_district === $existing_names[$name_lower]['district']) ? 'MATCH' : 'MISMATCH',
    ];
  } else {
    // Check if district exists in our database
    $csv_district_lower = strtolower($csv_district);
    if (isset($district_name_variations[$csv_district_lower])) {
      $mapped_district = $district_name_variations[$csv_district_lower];
      $new_terms[] = [
        'name' => $name,
        'machine_name' => $machine_name,
        'csv_district' => $csv_district,
        'mapped_district' => $mapped_district,
        'is_metro' => $is_metro,
        'csv_id' => $csv_term_id,
      ];
    } else {
      // District not found
      $unmapped_districts[] = [
        'name' => $name,
        'csv_district' => $csv_district,
      ];
    }
  }
}

// Display results
echo "=== ANALYSIS RESULTS ===\n\n";

echo "1. DUPLICATES (already in database): " . count($duplicates) . "\n";
if (count($duplicates) > 0) {
  echo "------------------------------------------------------------\n";
  foreach ($duplicates as $dup) {
    echo "  - {$dup['csv_name']} [{$dup['match']}]\n";
    echo "    CSV District: {$dup['csv_district']}\n";
    echo "    DB District:  {$dup['db_district']}\n";
    if ($dup['match'] === 'MISMATCH') {
      echo "    ⚠️  WARNING: District mismatch!\n";
    }
    echo "\n";
  }
}

echo "\n2. NEW TERMS TO IMPORT: " . count($new_terms) . "\n";
if (count($new_terms) > 10) {
  echo "------------------------------------------------------------\n";
  echo "First 10 samples:\n";
  foreach (array_slice($new_terms, 0, 10) as $term) {
    $district_note = ($term['csv_district'] !== $term['mapped_district'])
      ? " (mapped from '{$term['csv_district']}')" : "";
    echo "  - {$term['name']} → {$term['mapped_district']}{$district_note}\n";
  }
  echo "  ... and " . (count($new_terms) - 10) . " more\n";
}

echo "\n3. UNMAPPED DISTRICTS (not found in database): " . count($unmapped_districts) . "\n";
if (count($unmapped_districts) > 0) {
  echo "------------------------------------------------------------\n";
  $unique_districts = [];
  foreach ($unmapped_districts as $item) {
    $unique_districts[$item['csv_district']] = true;
  }
  echo "  Districts not found: " . implode(', ', array_keys($unique_districts)) . "\n";
  echo "  Affected terms: " . count($unmapped_districts) . "\n";
}

// District mapping check
echo "\n4. DISTRICT MAPPING SUMMARY:\n";
echo "------------------------------------------------------------\n";
$district_usage = [];
foreach ($new_terms as $term) {
  if (!isset($district_usage[$term['mapped_district']])) {
    $district_usage[$term['mapped_district']] = 0;
  }
  $district_usage[$term['mapped_district']]++;
}
arsort($district_usage);

foreach ($district_usage as $dist => $count) {
  echo "  $dist: $count upazilas\n";
}

// Summary
echo "\n=== IMPORT SUMMARY ===\n";
echo "Total CSV records: " . count($csv_data) . "\n";
echo "Existing in DB: " . count($duplicates) . "\n";
echo "Ready to import: " . count($new_terms) . "\n";
echo "Cannot import (unmapped): " . count($unmapped_districts) . "\n";

// Check for district mismatches in duplicates
$mismatches = array_filter($duplicates, function($d) { return $d['match'] === 'MISMATCH'; });
if (count($mismatches) > 0) {
  echo "\n⚠️  WARNING: " . count($mismatches) . " duplicate(s) have district mismatches!\n";
}

echo "\n✓ Analysis complete\n";
