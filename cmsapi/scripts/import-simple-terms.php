<?php

/**
 * @file
 * Script to import seed terms for simple taxonomies.
 *
 * Usage: ddev drush php:script scripts/import-simple-terms.php
 */

use Drupal\taxonomy\Entity\Term;

// Division terms.
$divisions = [
  'Dhaka',
  'Chattogram',
  'Rajshahi',
  'Khulna',
  'Barishal',
  'Sylhet',
  'Rangpur',
  'Mymensingh',
];

// Media Format terms.
$media_formats = [
  'Static Billboard',
  'Digital Billboard',
  'LED Screen',
  'Pole Sign',
  'Rooftop Sign',
  'Wall Sign',
  'Bridge Banner',
  'Foot Over Bridge Panel',
  'Median Panel',
  'Unipole',
  'Gantry Billboard',
  'Transit Shelter Branding',
  'Lamppost Branding',
  'Building Wrap',
  'Directional Signage',
];

// Placement Type terms.
$placement_types = [
  'Roadside Left',
  'Roadside Right',
  'Road Divider',
  'Median Strip',
  'Intersection Corner',
  'Flyover Side',
  'Flyover Underpass',
  'Roundabout',
  'Bridge Side',
  'Toll Plaza Approach',
  'Bus Stand Area',
  'Rail Crossing Area',
  'Market Front',
  'Building Facade',
  'Rooftop',
  'Entry Gate',
  'Exit Gate',
];

// Traffic Direction terms.
$traffic_directions = [
  'One Way',
  'Two Way',
  'Inbound',
  'Outbound',
  'Both Directions',
  'Northbound',
  'Southbound',
  'Eastbound',
  'Westbound',
];

// Visibility Class terms.
$visibility_classes = [
  'Premium',
  'High',
  'Medium',
  'Standard',
  'Limited',
];

// Illumination Type terms.
$illumination_types = [
  'Non Illuminated',
  'Front Lit',
  'Back Lit',
  'LED Illuminated',
  'Flood Lit',
  'Solar Lit',
];

// Booking Mode terms.
$booking_modes = [
  'Full Unit Booking',
  'Partial Unit Booking',
  'Cluster Booking',
  'Road Takeover',
  'Time Slot Booking',
  'Share of Voice',
  'Day Part Booking',
];

// Availability Status terms.
$availability_statuses = [
  'Available',
  'Reserved',
  'Booked',
  'Under Maintenance',
  'Temporarily Unavailable',
  'Blocked',
  'Archived',
];

// City Corporation terms.
$city_corporations = [
  'Dhaka North City Corporation',
  'Dhaka South City Corporation',
  'Chattogram City Corporation',
  'Khulna City Corporation',
  'Rajshahi City Corporation',
  'Sylhet City Corporation',
  'Barishal City Corporation',
  'Gazipur City Corporation',
  'Cumilla City Corporation',
  'Rangpur City Corporation',
  'Mymensingh City Corporation',
  'Narayanganj City Corporation',
];

/**
 * Helper function to create terms.
 */
function create_terms($vocabulary_id, $terms, &$stats) {
  foreach ($terms as $term_name) {
    // Check if term already exists.
    $existing = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'vid' => $vocabulary_id,
        'name' => $term_name,
      ]);

    if (!empty($existing)) {
      $stats['existing']++;
      continue;
    }

    // Create term.
    $term = Term::create([
      'vid' => $vocabulary_id,
      'name' => $term_name,
    ]);

    try {
      $term->save();
      $stats['created']++;
      echo "✓ Created: $term_name [$vocabulary_id]\n";
    }
    catch (\Exception $e) {
      $stats['failed']++;
      echo "✗ Failed: $term_name - {$e->getMessage()}\n";
    }
  }
}

// Initialize stats.
$stats = [
  'created' => 0,
  'existing' => 0,
  'failed' => 0,
];

// Create terms for each vocabulary.
echo "Creating Division terms...\n";
create_terms('division', $divisions, $stats);

echo "\nCreating Media Format terms...\n";
create_terms('media_format', $media_formats, $stats);

echo "\nCreating Placement Type terms...\n";
create_terms('placement_type', $placement_types, $stats);

echo "\nCreating Traffic Direction terms...\n";
create_terms('traffic_direction', $traffic_directions, $stats);

echo "\nCreating Visibility Class terms...\n";
create_terms('visibility_class', $visibility_classes, $stats);

echo "\nCreating Illumination Type terms...\n";
create_terms('illumination_type', $illumination_types, $stats);

echo "\nCreating Booking Mode terms...\n";
create_terms('booking_mode', $booking_modes, $stats);

echo "\nCreating Availability Status terms...\n";
create_terms('availability_status', $availability_statuses, $stats);

echo "\nCreating City Corporation terms...\n";
create_terms('city_corporation', $city_corporations, $stats);

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "IMPORT SUMMARY:\n";
echo "Created: {$stats['created']} terms\n";
echo "Already existed: {$stats['existing']} terms\n";
echo "Failed: {$stats['failed']} terms\n";
echo "\n✓ Simple taxonomy terms import complete!\n";
