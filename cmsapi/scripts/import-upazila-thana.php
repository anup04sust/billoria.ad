<?php

/**
 * @file
 * Script to import upazila_thana (sub-district) terms with district references.
 *
 * Usage: ddev drush php:script scripts/import-upazila-thana.php
 */

use Drupal\taxonomy\Entity\Term;

// Upazila/Thana data from content model.
$upazila_data = [
  // Dhaka District - Metropolitan Thanas
  ['name' => 'Gulshan Thana', 'district' => 'Dhaka', 'type' => 'thana', 'geo_code' => 'DH-GT'],
  ['name' => 'Motijheel Thana', 'district' => 'Dhaka', 'type' => 'thana', 'geo_code' => 'DH-MT'],
  ['name' => 'Tejgaon Thana', 'district' => 'Dhaka', 'type' => 'thana', 'geo_code' => 'DH-TG'],
  ['name' => 'Mirpur Thana', 'district' => 'Dhaka', 'type' => 'thana', 'geo_code' => 'DH-MP'],
  ['name' => 'Uttara Thana', 'district' => 'Dhaka', 'type' => 'thana', 'geo_code' => 'DH-UT'],

  // Dhaka District - Upazilas
  ['name' => 'Savar Upazila', 'district' => 'Dhaka', 'type' => 'upazila', 'geo_code' => 'DH-SV'],
  ['name' => 'Keraniganj Upazila', 'district' => 'Dhaka', 'type' => 'upazila', 'geo_code' => 'DH-KJ'],
  ['name' => 'Ashulia', 'district' => 'Dhaka', 'type' => 'upazila', 'geo_code' => 'DH-AS'],

  // Gazipur District
  ['name' => 'Tongi', 'district' => 'Gazipur', 'type' => 'upazila', 'geo_code' => 'GZ-TG'],
  ['name' => 'Kaliakair', 'district' => 'Gazipur', 'type' => 'upazila', 'geo_code' => 'GZ-KK'],
  ['name' => 'Kapasia', 'district' => 'Gazipur', 'type' => 'upazila', 'geo_code' => 'GZ-KP'],

  // Narayanganj District
  ['name' => 'Rupganj', 'district' => 'Narayanganj', 'type' => 'upazila', 'geo_code' => 'NR-RG'],
  ['name' => 'Sonargaon', 'district' => 'Narayanganj', 'type' => 'upazila', 'geo_code' => 'NR-SG'],
  ['name' => 'Araihazar', 'district' => 'Narayanganj', 'type' => 'upazila', 'geo_code' => 'NR-AH'],

  // Chattogram District - Metropolitan Thanas
  ['name' => 'Panchlaish Thana', 'district' => 'Chattogram', 'type' => 'thana', 'geo_code' => 'CT-PC'],
  ['name' => 'Kotwali Thana', 'district' => 'Chattogram', 'type' => 'thana', 'geo_code' => 'CT-KW'],
  ['name' => 'Pahartali Thana', 'district' => 'Chattogram', 'type' => 'thana', 'geo_code' => 'CT-PH'],
  ['name' => 'Agrabad Thana', 'district' => 'Chattogram', 'type' => 'thana', 'geo_code' => 'CT-AG'],

  // Cox's Bazar District
  ['name' => "Cox's Bazar Sadar", 'district' => "Cox's Bazar", 'type' => 'upazila', 'geo_code' => 'CB-SD'],
  ['name' => 'Teknaf', 'district' => "Cox's Bazar", 'type' => 'upazila', 'geo_code' => 'CB-TK'],
  ['name' => 'Ramu', 'district' => "Cox's Bazar", 'type' => 'upazila', 'geo_code' => 'CB-RM'],

  // Khulna District
  ['name' => 'Khulna Sadar', 'district' => 'Khulna', 'type' => 'upazila', 'geo_code' => 'KH-SD'],
  ['name' => 'Daulatpur', 'district' => 'Khulna', 'type' => 'upazila', 'geo_code' => 'KH-DP'],
  ['name' => 'Khalishpur', 'district' => 'Khulna', 'type' => 'thana', 'geo_code' => 'KH-KL'],

  // Sylhet District
  ['name' => 'Sylhet Sadar', 'district' => 'Sylhet', 'type' => 'upazila', 'geo_code' => 'SY-SD'],
  ['name' => 'South Surma', 'district' => 'Sylhet', 'type' => 'upazila', 'geo_code' => 'SY-SS'],
  ['name' => 'Companiganj', 'district' => 'Sylhet', 'type' => 'upazila', 'geo_code' => 'SY-CG'],

  // Rajshahi District
  ['name' => 'Rajshahi Sadar', 'district' => 'Rajshahi', 'type' => 'upazila', 'geo_code' => 'RJ-SD'],
  ['name' => 'Boalia Thana', 'district' => 'Rajshahi', 'type' => 'thana', 'geo_code' => 'RJ-BL'],
  ['name' => 'Motihar Thana', 'district' => 'Rajshahi', 'type' => 'thana', 'geo_code' => 'RJ-MH'],

  // Rangpur District
  ['name' => 'Rangpur Sadar', 'district' => 'Rangpur', 'type' => 'upazila', 'geo_code' => 'RG-SD'],

  // Mymensingh District
  ['name' => 'Mymensingh Sadar', 'district' => 'Mymensingh', 'type' => 'upazila', 'geo_code' => 'MM-SD'],

  // Barishal District
  ['name' => 'Barishal Sadar', 'district' => 'Barishal', 'type' => 'upazila', 'geo_code' => 'BR-SD'],

  // Cumilla District
  ['name' => 'Cumilla Sadar', 'district' => 'Cumilla', 'type' => 'upazila', 'geo_code' => 'CM-SD'],
];

// Load district terms for reference mapping.
$district_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'district']);

$district_map = [];
foreach ($district_terms as $term) {
  $district_map[$term->getName()] = $term->id();
}

$created = 0;
$existing = 0;
$failed = 0;

echo "Importing Upazila/Thana terms with District references...\n\n";

foreach ($upazila_data as $data) {
  // Check if term exists.
  $existing_terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'vid' => 'upazila_thana',
      'name' => $data['name'],
    ]);

  if (!empty($existing_terms)) {
    $existing++;
    continue;
  }

  // Get district reference.
  $district_tid = $district_map[$data['district']] ?? NULL;

  if (!$district_tid) {
    echo "✗ District not found: {$data['district']} for {$data['name']}\n";
    $failed++;
    continue;
  }

  // Create upazila/thana term.
  $term = Term::create([
    'vid' => 'upazila_thana',
    'name' => $data['name'],
    'field_district' => ['target_id' => $district_tid],
    'field_geo_code' => $data['geo_code'],
    'field_type' => $data['type'],
  ]);

  try {
    $term->save();
    $created++;
    echo "✓ Created: {$data['name']} [{$data['district']}] - {$data['type']}\n";
  }
  catch (\Exception $e) {
    $failed++;
    echo "✗ Failed: {$data['name']} - {$e->getMessage()}\n";
  }
}

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "UPAZILA/THANA IMPORT SUMMARY:\n";
echo "Created: $created terms\n";
echo "Already existed: $existing terms\n";
echo "Failed: $failed terms\n";
echo "\n✓ Upazila/Thana import complete!\n";
echo "\nNote: Bangladesh has 495 upazilas total. These are key commercial centers.\n";
echo "Expand coverage as needed for billboard inventory expansion.\n";
