<?php

/**
 * @file
 * Script to import road_type terms with codes.
 *
 * Usage: ddev drush php:script scripts/import-road-types.php
 */

use Drupal\taxonomy\Entity\Term;

// Road type data from content model.
$road_types = [
  ['name' => 'National Highway', 'code' => 'national_highway', 'description' => 'Major intercity roads'],
  ['name' => 'Regional Highway', 'code' => 'regional_highway', 'description' => 'Regional connectors'],
  ['name' => 'Zila Road', 'code' => 'zila_road', 'description' => 'District roads'],
  ['name' => 'City Road', 'code' => 'city_road', 'description' => 'Urban roads'],
  ['name' => 'Bypass Road', 'code' => 'bypass_road', 'description' => 'Bypass corridors'],
  ['name' => 'Expressway', 'code' => 'expressway', 'description' => 'Controlled fast corridors'],
  ['name' => 'Flyover', 'code' => 'flyover', 'description' => 'Elevated corridors'],
  ['name' => 'Service Road', 'code' => 'service_road', 'description' => 'Parallel local access'],
  ['name' => 'Link Road', 'code' => 'link_road', 'description' => 'Connector route'],
  ['name' => 'Connector Road', 'code' => 'connector_road', 'description' => 'Short corridor link'],
  ['name' => 'Bridge Approach Road', 'code' => 'bridge_approach_road', 'description' => 'Major bridge access'],
  ['name' => 'Industrial Access Road', 'code' => 'industrial_access_road', 'description' => 'EPZ / industrial area'],
  ['name' => 'Port Access Road', 'code' => 'port_access_road', 'description' => 'Port cargo areas'],
  ['name' => 'Airport Access Road', 'code' => 'airport_access_road', 'description' => 'Airport approach'],
  ['name' => 'Ring Road', 'code' => 'ring_road', 'description' => 'Ring corridors'],
  ['name' => 'Outer Ring Road', 'code' => 'outer_ring_road', 'description' => 'Outer urban loop'],
  ['name' => 'Inner Ring Road', 'code' => 'inner_ring_road', 'description' => 'Inner urban loop'],
  ['name' => 'Commercial Corridor', 'code' => 'commercial_corridor', 'description' => 'Business-heavy route'],
  ['name' => 'Urban Arterial Road', 'code' => 'urban_arterial_road', 'description' => 'Major city artery'],
  ['name' => 'Urban Collector Road', 'code' => 'urban_collector_road', 'description' => 'Collector route'],
];

$created = 0;
$existing = 0;
$failed = 0;

echo "Creating Road Type terms...\n\n";

foreach ($road_types as $data) {
  // Check if term exists.
  $existing_terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'vid' => 'road_type',
      'name' => $data['name'],
    ]);

  if (!empty($existing_terms)) {
    $existing++;
    continue;
  }

  // Create term with custom fields.
  $term = Term::create([
    'vid' => 'road_type',
    'name' => $data['name'],
    'field_code' => $data['code'],
    'field_description' => [
      'value' => $data['description'],
      'format' => 'plain_text',
    ],
  ]);

  try {
    $term->save();
    $created++;
    echo "✓ Created: {$data['name']} ({$data['code']})\n";
  }
  catch (\Exception $e) {
    $failed++;
    echo "✗ Failed: {$data['name']} - {$e->getMessage()}\n";
  }
}

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "ROAD TYPE IMPORT SUMMARY:\n";
echo "Created: $created terms\n";
echo "Already existed: $existing terms\n";
echo "Failed: $failed terms\n";
echo "\n✓ Road type import complete!\n";
