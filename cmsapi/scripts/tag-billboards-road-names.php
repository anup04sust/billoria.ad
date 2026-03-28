<?php

/**
 * @file
 * Script to auto-tag billboards with road_name terms by matching titles.
 *
 * Usage: ddev drush php:script scripts/tag-billboards-road-names.php
 */

use Drupal\node\Entity\Node;

// Load all road_name terms.
$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$road_terms = $term_storage->loadByProperties(['vid' => 'road_name']);

// Build lookup: lowercase name => tid, sorted longest-first for greedy match.
$road_lookup = [];
foreach ($road_terms as $term) {
  $road_lookup[mb_strtolower($term->getName())] = (int) $term->id();
}
// Sort by key length descending so "Dhaka–Chittagong Highway" matches before "Airport Road".
uksort($road_lookup, function ($a, $b) {
  return mb_strlen($b) - mb_strlen($a);
});

echo "Loaded " . count($road_lookup) . " road name terms.\n";

// Load all road_type terms for fallback matching.
$road_type_terms = $term_storage->loadByProperties(['vid' => 'road_type']);
$type_lookup = [];
foreach ($road_type_terms as $term) {
  $type_lookup[mb_strtolower($term->getName())] = (int) $term->id();
}
uksort($type_lookup, function ($a, $b) {
  return mb_strlen($b) - mb_strlen($a);
});

// Additional keyword → road_type mappings for generic billboard titles.
$keyword_type_map = [
  'highway junction' => 'National Highway',
  'highway' => 'National Highway',
  'expressway' => 'Expressway',
  'flyover' => 'Flyover',
  'overpass' => 'Flyover',
  'bypass' => 'Bypass Road',
  'ring road' => 'Ring Road',
  'station road' => 'City Road',
  'main road' => 'City Road',
  'avenue' => 'Urban Arterial Road',
  'bridge side' => 'Bridge Approach Road',
  'underpass' => 'Flyover',
  'crossing' => 'Urban Arterial Road',
  'circle' => 'Urban Collector Road',
  'market area' => 'Commercial Corridor',
  'shopping district' => 'Commercial Corridor',
  'city center' => 'City Road',
  'industrial' => 'Industrial Access Road',
];

echo "Loaded " . count($type_lookup) . " road type terms.\n\n";

// Load all billboard nodes.
$nids = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'billboard')
  ->execute();

$tagged = 0;
$skipped = 0;
$no_match = 0;
$total = count($nids);

echo "Processing $total billboards...\n\n";

foreach (Node::loadMultiple($nids) as $node) {
  $changed = FALSE;
  $title = mb_strtolower($node->getTitle());

  // Also check area_zone label if available.
  $area_zone_text = '';
  if ($node->hasField('field_area_zone') && !$node->get('field_area_zone')->isEmpty()) {
    $zone_term = $node->get('field_area_zone')->entity;
    if ($zone_term) {
      $area_zone_text = mb_strtolower($zone_term->getName());
    }
  }

  $search_text = $title . ' ' . $area_zone_text;

  // 1) Try matching road_name (specific road).
  if ($node->get('field_road_name')->isEmpty()) {
    foreach ($road_lookup as $road_name => $tid) {
      if (mb_strpos($search_text, $road_name) !== FALSE) {
        $node->set('field_road_name', ['target_id' => $tid]);
        $changed = TRUE;

        // Also set road_type from the road_name term reference.
        if ($node->get('field_road_type')->isEmpty()) {
          $road_term = $term_storage->load($tid);
          if ($road_term && $road_term->hasField('field_road_type') && !$road_term->get('field_road_type')->isEmpty()) {
            $node->set('field_road_type', ['target_id' => $road_term->get('field_road_type')->target_id]);
          }
        }

        echo "✓ Road name: \"{$node->getTitle()}\" → $road_name\n";
        break;
      }
    }
  }

  // 2) Try matching road_type directly from title keywords (fallback).
  if ($node->get('field_road_type')->isEmpty()) {
    // First try exact road_type term names.
    foreach ($type_lookup as $type_name => $tid) {
      if (mb_strpos($search_text, $type_name) !== FALSE) {
        $node->set('field_road_type', ['target_id' => $tid]);
        $changed = TRUE;
        echo "✓ Road type (exact): \"{$node->getTitle()}\" → $type_name\n";
        break;
      }
    }

    // Still empty? Try keyword mapping.
    if ($node->get('field_road_type')->isEmpty()) {
      foreach ($keyword_type_map as $keyword => $type_name) {
        if (mb_strpos($search_text, $keyword) !== FALSE) {
          $mapped_name = mb_strtolower($type_name);
          if (isset($type_lookup[$mapped_name])) {
            $node->set('field_road_type', ['target_id' => $type_lookup[$mapped_name]]);
            $changed = TRUE;
            echo "✓ Road type (keyword): \"{$node->getTitle()}\" → $type_name (via '$keyword')\n";
            break;
          }
        }
      }
    }
  }

  if ($changed) {
    $node->save();
    $tagged++;
  }
  elseif (!$node->get('field_road_name')->isEmpty() || !$node->get('field_road_type')->isEmpty()) {
    $skipped++;
  }
  else {
    $no_match++;
    echo "  No match: \"{$node->getTitle()}\"\n";
  }
}

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "BILLBOARD ROAD TAGGING SUMMARY:\n";
echo "Tagged: $tagged billboards\n";
echo "Already tagged (skipped): $skipped billboards\n";
echo "No match found: $no_match billboards\n";
echo "Total: $total billboards\n";
echo "\n✓ Road name tagging complete!\n";
