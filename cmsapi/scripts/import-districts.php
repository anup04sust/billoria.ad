<?php

/**
 * @file
 * Script to import district terms with division references.
 *
 * Usage: ddev drush php:script scripts/import-districts.php
 */

use Drupal\taxonomy\Entity\Term;

// District data from content model with division mapping.
$districts_data = [
  // Dhaka Division
  ['name' => 'Dhaka', 'division' => 'Dhaka', 'geo_code' => '13'],
  ['name' => 'Gazipur', 'division' => 'Dhaka', 'geo_code' => '33'],
  ['name' => 'Narayanganj', 'division' => 'Dhaka', 'geo_code' => '67'],
  ['name' => 'Narsingdi', 'division' => 'Dhaka', 'geo_code' => '68'],
  ['name' => 'Tangail', 'division' => 'Dhaka', 'geo_code' => '92'],
  ['name' => 'Faridpur', 'division' => 'Dhaka', 'geo_code' => '29'],
  ['name' => 'Gopalganj', 'division' => 'Dhaka', 'geo_code' => '35'],
  ['name' => 'Kishoreganj', 'division' => 'Dhaka', 'geo_code' => '48'],
  ['name' => 'Madaripur', 'division' => 'Dhaka', 'geo_code' => '49'],
  ['name' => 'Manikganj', 'division' => 'Dhaka', 'geo_code' => '51'],
  ['name' => 'Munshiganj', 'division' => 'Dhaka', 'geo_code' => '54'],
  ['name' => 'Rajbari', 'division' => 'Dhaka', 'geo_code' => '76'],
  ['name' => 'Shariatpur', 'division' => 'Dhaka', 'geo_code' => '86'],

  // Chattogram Division
  ['name' => 'Chattogram', 'division' => 'Chattogram', 'geo_code' => '03'],
  ['name' => "Cox's Bazar", 'division' => 'Chattogram', 'geo_code' => '11'],
  ['name' => 'Cumilla', 'division' => 'Chattogram', 'geo_code' => '08'],
  ['name' => 'Brahmanbaria', 'division' => 'Chattogram', 'geo_code' => '04'],
  ['name' => 'Chandpur', 'division' => 'Chattogram', 'geo_code' => '09'],
  ['name' => 'Feni', 'division' => 'Chattogram', 'geo_code' => '26'],
  ['name' => 'Khagrachhari', 'division' => 'Chattogram', 'geo_code' => '44'],
  ['name' => 'Lakshmipur', 'division' => 'Chattogram', 'geo_code' => '50'],
  ['name' => 'Noakhali', 'division' => 'Chattogram', 'geo_code' => '75'],
  ['name' => 'Rangamati', 'division' => 'Chattogram', 'geo_code' => '77'],
  ['name' => 'Bandarban', 'division' => 'Chattogram', 'geo_code' => '01'],

  // Khulna Division
  ['name' => 'Khulna', 'division' => 'Khulna', 'geo_code' => '41'],
  ['name' => 'Jessore', 'division' => 'Khulna', 'geo_code' => '47'],
  ['name' => 'Bagerhat', 'division' => 'Khulna', 'geo_code' => '05'],
  ['name' => 'Satkhira', 'division' => 'Khulna', 'geo_code' => '85'],
  ['name' => 'Chuadanga', 'division' => 'Khulna', 'geo_code' => '12'],
  ['name' => 'Jhenaidah', 'division' => 'Khulna', 'geo_code' => '46'],
  ['name' => 'Kushtia', 'division' => 'Khulna', 'geo_code' => '45'],
  ['name' => 'Magura', 'division' => 'Khulna', 'geo_code' => '52'],
  ['name' => 'Meherpur', 'division' => 'Khulna', 'geo_code' => '55'],
  ['name' => 'Narail', 'division' => 'Khulna', 'geo_code' => '69'],

  // Rajshahi Division
  ['name' => 'Rajshahi', 'division' => 'Rajshahi', 'geo_code' => '81'],
  ['name' => 'Bogura', 'division' => 'Rajshahi', 'geo_code' => '60'],
  ['name' => 'Chapainawabganj', 'division' => 'Rajshahi', 'geo_code' => '24'],
  ['name' => 'Naogaon', 'division' => 'Rajshahi', 'geo_code' => '64'],
  ['name' => 'Natore', 'division' => 'Rajshahi', 'geo_code' => '65'],
  ['name' => 'Pabna', 'division' => 'Rajshahi', 'geo_code' => '72'],
  ['name' => 'Sirajganj', 'division' => 'Rajshahi', 'geo_code' => '89'],
  ['name' => 'Joypurhat', 'division' => 'Rajshahi', 'geo_code' => '39'],

  // Rangpur Division
  ['name' => 'Rangpur', 'division' => 'Rangpur', 'geo_code' => '55'],
  ['name' => 'Dinajpur', 'division' => 'Rangpur', 'geo_code' => '27'],
  ['name' => 'Gaibandha', 'division' => 'Rangpur', 'geo_code' => '32'],
  ['name' => 'Kurigram', 'division' => 'Rangpur', 'geo_code' => '43'],
  ['name' => 'Lalmonirhat', 'division' => 'Rangpur', 'geo_code' => '49'],
  ['name' => 'Nilphamari', 'division' => 'Rangpur', 'geo_code' => '73'],
  ['name' => 'Panchagarh', 'division' => 'Rangpur', 'geo_code' => '59'],
  ['name' => 'Thakurgaon', 'division' => 'Rangpur', 'geo_code' => '93'],

  // Sylhet Division
  ['name' => 'Sylhet', 'division' => 'Sylhet', 'geo_code' => '90'],
  ['name' => 'Habiganj', 'division' => 'Sylhet', 'geo_code' => '36'],
  ['name' => 'Moulvibazar', 'division' => 'Sylhet', 'geo_code' => '53'],
  ['name' => 'Sunamganj', 'division' => 'Sylhet', 'geo_code' => '91'],

  // Mymensingh Division
  ['name' => 'Mymensingh', 'division' => 'Mymensingh', 'geo_code' => '61'],
  ['name' => 'Jamalpur', 'division' => 'Mymensingh', 'geo_code' => '37'],
  ['name' => 'Netrokona', 'division' => 'Mymensingh', 'geo_code' => '56'],
  ['name' => 'Sherpur', 'division' => 'Mymensingh', 'geo_code' => '88'],

  // Barishal Division
  ['name' => 'Barishal', 'division' => 'Barishal', 'geo_code' => '06'],
  ['name' => 'Barguna', 'division' => 'Barishal', 'geo_code' => '07'],
  ['name' => 'Bhola', 'division' => 'Barishal', 'geo_code' => '10'],
  ['name' => 'Jhalokati', 'division' => 'Barishal', 'geo_code' => '42'],
  ['name' => 'Patuakhali', 'division' => 'Barishal', 'geo_code' => '78'],
  ['name' => 'Pirojpur', 'division' => 'Barishal', 'geo_code' => '79'],
];

// Load division terms for reference mapping.
$division_terms = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term')
  ->loadByProperties(['vid' => 'division']);

$division_map = [];
foreach ($division_terms as $term) {
  $division_map[$term->getName()] = $term->id();
}

$created = 0;
$existing = 0;
$failed = 0;

echo "Creating District terms with Division references...\n\n";

foreach ($districts_data as $data) {
  // Check if term exists.
  $existing_terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'vid' => 'district',
      'name' => $data['name'],
    ]);

  if (!empty($existing_terms)) {
    $existing++;
    continue;
  }

  // Get division reference.
  $division_tid = $division_map[$data['division']] ?? NULL;

  if (!$division_tid) {
    echo "✗ Division not found for {$data['name']}: {$data['division']}\n";
    $failed++;
    continue;
  }

  // Create district term.
  $term = Term::create([
    'vid' => 'district',
    'name' => $data['name'],
    'field_division' => ['target_id' => $division_tid],
    'field_geo_code' => $data['geo_code'],
  ]);

  try {
    $term->save();
    $created++;
    echo "✓ Created: {$data['name']} → {$data['division']} (code: {$data['geo_code']})\n";
  }
  catch (\Exception $e) {
    $failed++;
    echo "✗ Failed: {$data['name']} - {$e->getMessage()}\n";
  }
}

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "DISTRICT IMPORT SUMMARY:\n";
echo "Created: $created terms\n";
echo "Already existed: $existing terms\n";
echo "Failed: $failed terms\n";
echo "\n✓ District import complete!\n";
