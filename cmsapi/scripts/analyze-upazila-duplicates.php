<?php

/**
 * Detailed analysis of duplicate upazila names across districts
 */

// Read CSV and find all duplicate names
$csv_file = '/var/www/billoria.ad/cmsapi/bangladesh_sub_district_upazila_master.csv';
$csv_data = array_map('str_getcsv', file($csv_file));
array_shift($csv_data); // Remove header

echo "=== DUPLICATE UPAZILA NAME ANALYSIS ===\n\n";
echo "Analyzing 495 upazilas for duplicate names...\n\n";

// Group by upazila name
$name_groups = [];
foreach ($csv_data as $row) {
  $name = trim($row[1]);
  $district = trim($row[3]);
  $division = trim($row[4]);

  if (!isset($name_groups[$name])) {
    $name_groups[$name] = [];
  }
  $name_groups[$name][] = [
    'district' => $district,
    'division' => $division,
  ];
}

// Find duplicates
$duplicates = array_filter($name_groups, function($locations) {
  return count($locations) > 1;
});

echo "Found " . count($duplicates) . " upazila names that appear in multiple districts:\n";
echo "============================================================\n\n";

foreach ($duplicates as $name => $locations) {
  echo "**$name** appears in " . count($locations) . " districts:\n";
  foreach ($locations as $loc) {
    echo "  - {$loc['district']} ({$loc['division']} division)\n";
  }
  echo "\n";
}

echo "\n=== RECOMMENDATION ===\n";
echo "These duplicate names need unique identifiers.\n";
echo "Suggested format: \"Upazila Name (District)\"\n";
echo "Example: \"Daulatpur (Manikganj)\", \"Daulatpur (Kushtia)\"\n";
