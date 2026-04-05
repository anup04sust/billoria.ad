<?php

/**
 * @file
 * Creates Dhaka thana-level upazila_thana terms and assigns city corporation.
 *
 * Usage: ddev drush php:script scripts/update-dhaka-thana-city-corporation.php
 */

use Drupal\taxonomy\Entity\Term;

$storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

// Dhaka district ID.
$dhaka_district_id = 107;

// City Corporation IDs.
$dncc_id = 75; // Dhaka North City Corporation
$dscc_id = 76; // Dhaka South City Corporation

// Dhaka North Zone (DNCC).
$north_thanas = [
  'Adabor',
  'Badda',
  'Banani',
  'Bhashantek',
  'Cantonment',
  'Darus Salam',
  'Dakshinkhan',
  'Gulshan',
  'Hatirjheel',
  'Kafrul',
  'Khilkhet',
  'Mirpur Model',
  'Pallabi',
  'Rupnagar',
  'Sabujbag',
  'Shah Ali',
  'Tejgaon Industrial Area',
  'Turag',
  'Uttara East',
  'Uttara West',
  'Uttarkhan',
];

// Dhaka South Zone (DSCC).
$south_thanas = [
  'Bangshal',
  'Chawkbazar',
  'Demra',
  'Dhanmondi',
  'Gandaria',
  'Hazaribagh',
  'Jatrabari',
  'Kadamtali',
  'Kamrangirchar',
  'Khilgaon',
  'Kotwali',
  'Lalbagh',
  'Motijheel',
  'Mugda',
  'New Market',
  'Paltan',
  'Ramna',
  'Shahbag',
  'Shyampur',
  'Sutrapur',
  'Wari',
];

$created = 0;
$updated = 0;
$skipped = 0;

echo "=== Processing Dhaka North City Corporation (DNCC) thanas ===\n\n";

foreach ($north_thanas as $name) {
  $result = process_thana($storage, $name, $dhaka_district_id, $dncc_id, 'Dhaka North City Corporation');
  if ($result === 'created') $created++;
  elseif ($result === 'updated') $updated++;
  else $skipped++;
}

echo "\n=== Processing Dhaka South City Corporation (DSCC) thanas ===\n\n";

foreach ($south_thanas as $name) {
  $result = process_thana($storage, $name, $dhaka_district_id, $dscc_id, 'Dhaka South City Corporation');
  if ($result === 'created') $created++;
  elseif ($result === 'updated') $updated++;
  else $skipped++;
}

echo "\n=== Done: $created created, $updated updated, $skipped already correct ===\n";

/**
 * Create or update a thana term.
 */
function process_thana($storage, string $name, int $district_id, int $cc_id, string $cc_label): string {
  // Search for existing term by name in upazila_thana vocab under Dhaka district.
  $existing = $storage->loadByProperties([
    'vid' => 'upazila_thana',
    'name' => $name,
    'field_district' => $district_id,
  ]);

  if (!empty($existing)) {
    $term = reset($existing);
    // Check if city corporation already set correctly.
    $current_cc = (!$term->get('field_city_corporation')->isEmpty())
      ? (int) $term->get('field_city_corporation')->target_id : NULL;

    if ($current_cc === $cc_id) {
      echo "— SKIP: '$name' — already assigned to $cc_label\n";
      return 'skipped';
    }

    $term->set('field_city_corporation', $cc_id);
    $term->set('field_type', 'thana');
    $term->save();
    echo "✓ UPDATED: '$name' → $cc_label (id:{$term->id()})\n";
    return 'updated';
  }

  // Also check without district filter (might exist under different name match).
  $loose = $storage->loadByProperties([
    'vid' => 'upazila_thana',
    'name' => $name,
  ]);

  if (!empty($loose)) {
    $term = reset($loose);
    $term->set('field_district', $district_id);
    $term->set('field_city_corporation', $cc_id);
    $term->set('field_type', 'thana');
    $term->save();
    echo "✓ UPDATED (reassigned): '$name' → Dhaka + $cc_label (id:{$term->id()})\n";
    return 'updated';
  }

  // Create new term.
  $term = Term::create([
    'vid' => 'upazila_thana',
    'name' => $name,
    'status' => 1,
    'field_district' => $district_id,
    'field_city_corporation' => $cc_id,
    'field_type' => 'thana',
  ]);
  $term->save();
  echo "✓ CREATED: '$name' → $cc_label (id:{$term->id()})\n";
  return 'created';
}
