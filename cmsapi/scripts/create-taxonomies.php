<?php

/**
 * @file
 * Script to create required taxonomies for Billboard content type.
 *
 * Usage: ddev drush php:script scripts/create-taxonomies.php
 */

use Drupal\taxonomy\Entity\Vocabulary;

// Define vocabularies to create.
$vocabularies = [
  'media_format' => [
    'label' => 'Media Format',
    'description' => 'Billboard media format types (Static, LED, Digital, etc.)',
    'weight' => 0,
  ],
  'placement_type' => [
    'label' => 'Placement Type',
    'description' => 'Billboard placement location types (Roadside, Divider, Rooftop, etc.)',
    'weight' => 1,
  ],
  'road_type' => [
    'label' => 'Road Type',
    'description' => 'Road classification types (National Highway, City Road, etc.)',
    'weight' => 2,
  ],
  'division' => [
    'label' => 'Division',
    'description' => 'Bangladesh administrative divisions',
    'weight' => 3,
  ],
  'district' => [
    'label' => 'District',
    'description' => 'Bangladesh districts',
    'weight' => 4,
  ],
  'upazila_thana' => [
    'label' => 'Upazila / Thana',
    'description' => 'Bangladesh sub-districts (Upazila/Thana level)',
    'weight' => 5,
  ],
  'city_corporation' => [
    'label' => 'City Corporation',
    'description' => 'Bangladesh city corporations',
    'weight' => 6,
  ],
  'area_zone' => [
    'label' => 'Area / Zone',
    'description' => 'Commercial areas and neighborhood zones',
    'weight' => 7,
  ],
  'road_name' => [
    'label' => 'Road Name',
    'description' => 'Specific road and highway names',
    'weight' => 8,
  ],
  'traffic_direction' => [
    'label' => 'Traffic Direction',
    'description' => 'Traffic flow directions',
    'weight' => 9,
  ],
  'visibility_class' => [
    'label' => 'Visibility Class',
    'description' => 'Billboard visibility quality rating',
    'weight' => 10,
  ],
  'illumination_type' => [
    'label' => 'Illumination Type',
    'description' => 'Billboard lighting types',
    'weight' => 11,
  ],
  'booking_mode' => [
    'label' => 'Booking Mode',
    'description' => 'Billboard booking/reservation modes',
    'weight' => 12,
  ],
  'availability_status' => [
    'label' => 'Availability Status',
    'description' => 'Billboard availability states',
    'weight' => 13,
  ],
];

$created = [];
$existing = [];

foreach ($vocabularies as $vid => $vocab_data) {
  // Check if vocabulary already exists.
  $existing_vocab = Vocabulary::load($vid);

  if ($existing_vocab) {
    $existing[] = $vocab_data['label'] . " ($vid)";
    echo "✓ Vocabulary '$vid' already exists.\n";
    continue;
  }

  // Create vocabulary.
  $vocabulary = Vocabulary::create([
    'vid' => $vid,
    'name' => $vocab_data['label'],
    'description' => $vocab_data['description'],
    'weight' => $vocab_data['weight'],
  ]);

  try {
    $vocabulary->save();
    $created[] = $vocab_data['label'] . " ($vid)";
    echo "✓ Created vocabulary: {$vocab_data['label']} ($vid)\n";
  }
  catch (\Exception $e) {
    echo "✗ Failed to create $vid: {$e->getMessage()}\n";
  }
}

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "SUMMARY:\n";
echo "Created: " . count($created) . " vocabularies\n";
echo "Existing: " . count($existing) . " vocabularies\n";

if (!empty($created)) {
  echo "\nNewly created:\n";
  foreach ($created as $vocab) {
    echo "  - $vocab\n";
  }
}

if (!empty($existing)) {
  echo "\nAlready existed:\n";
  foreach ($existing as $vocab) {
    echo "  - $vocab\n";
  }
}

echo "\n✓ Taxonomy vocabulary creation complete!\n";
