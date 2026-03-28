<?php

/**
 * @file
 * Script to import road_name terms with references to road_type, division, and district.
 *
 * Usage: ddev drush php:script scripts/import-road-names.php
 */

use Drupal\taxonomy\Entity\Term;

/**
 * Helper: look up a taxonomy term TID by vocabulary + name.
 */
function find_term_id(string $vid, string $name): ?int {
  $terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties(['vid' => $vid, 'name' => $name]);
  if (!empty($terms)) {
    return (int) reset($terms)->id();
  }
  return NULL;
}

// Road name data: name, code, road_type (ref), division (ref), district (ref).
$road_names = [
  // ── Dhaka Division ──
  ['name' => 'Airport Road', 'code' => 'airport_road_dhaka', 'road_type' => 'Airport Access Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Mirpur Road', 'code' => 'mirpur_road', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Pragati Sarani', 'code' => 'pragati_sarani', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Panthapath', 'code' => 'panthapath', 'road_type' => 'Commercial Corridor', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Green Road', 'code' => 'green_road', 'road_type' => 'Commercial Corridor', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Satmasjid Road', 'code' => 'satmasjid_road', 'road_type' => 'Urban Collector Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Elephant Road', 'code' => 'elephant_road', 'road_type' => 'Commercial Corridor', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Dhanmondi Road', 'code' => 'dhanmondi_road', 'road_type' => 'Urban Collector Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Gulshan Avenue', 'code' => 'gulshan_avenue', 'road_type' => 'Commercial Corridor', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Banani Road', 'code' => 'banani_road', 'road_type' => 'Urban Collector Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Mohakhali Flyover', 'code' => 'mohakhali_flyover', 'road_type' => 'Flyover', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Hanif Flyover', 'code' => 'hanif_flyover', 'road_type' => 'Flyover', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Moghbazar–Mouchak Flyover', 'code' => 'moghbazar_mouchak_flyover', 'road_type' => 'Flyover', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Jatrabari Flyover', 'code' => 'jatrabari_flyover', 'road_type' => 'Flyover', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Hatirjheel Belt', 'code' => 'hatirjheel_belt', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Rampura Link Road', 'code' => 'rampura_link_road', 'road_type' => 'Link Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Farmgate Road', 'code' => 'farmgate_road', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Shahbag Road', 'code' => 'shahbag_road', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Bijoy Sarani', 'code' => 'bijoy_sarani', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Rokeya Sarani', 'code' => 'rokeya_sarani', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Kazi Nazrul Islam Avenue', 'code' => 'kazi_nazrul_islam_ave', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Manik Mia Avenue', 'code' => 'manik_mia_avenue', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Progoti Sarani', 'code' => 'progoti_sarani', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Maulana Bhasani Road', 'code' => 'maulana_bhasani_road', 'road_type' => 'City Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Shahid Tajuddin Ahmed Sarani', 'code' => 'tajuddin_ahmed_sarani', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'DIT Road', 'code' => 'dit_road', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'VIP Road Uttara', 'code' => 'vip_road_uttara', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],

  // ── Dhaka Division — Highways ──
  ['name' => 'Dhaka–Chittagong Highway', 'code' => 'dhaka_chittagong_highway', 'road_type' => 'National Highway', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Dhaka–Mymensingh Highway', 'code' => 'dhaka_mymensingh_highway', 'road_type' => 'National Highway', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Dhaka–Sylhet Highway', 'code' => 'dhaka_sylhet_highway', 'road_type' => 'National Highway', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Dhaka–Aricha Highway', 'code' => 'dhaka_aricha_highway', 'road_type' => 'National Highway', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Dhaka–Mawa Expressway', 'code' => 'dhaka_mawa_expressway', 'road_type' => 'Expressway', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Padma Bridge Expressway', 'code' => 'padma_bridge_expressway', 'road_type' => 'Expressway', 'division' => 'Dhaka', 'district' => 'Munshiganj'],
  ['name' => 'Bangabandhu Expressway', 'code' => 'bangabandhu_expressway', 'road_type' => 'Expressway', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Tongi–Ashulia Expressway', 'code' => 'tongi_ashulia_expressway', 'road_type' => 'Expressway', 'division' => 'Dhaka', 'district' => 'Gazipur'],
  ['name' => 'Dhaka Bypass Road', 'code' => 'dhaka_bypass_road', 'road_type' => 'Bypass Road', 'division' => 'Dhaka', 'district' => 'Dhaka'],
  ['name' => 'Kanchpur Bridge Approach', 'code' => 'kanchpur_bridge_approach', 'road_type' => 'Bridge Approach Road', 'division' => 'Dhaka', 'district' => 'Narayanganj'],

  // ── Gazipur ──
  ['name' => 'Dhaka–Tangail Highway', 'code' => 'dhaka_tangail_highway', 'road_type' => 'National Highway', 'division' => 'Dhaka', 'district' => 'Gazipur'],
  ['name' => 'Joydebpur Chowrasta Road', 'code' => 'joydebpur_chowrasta_road', 'road_type' => 'City Road', 'division' => 'Dhaka', 'district' => 'Gazipur'],
  ['name' => 'Gazipur–Sreepur Road', 'code' => 'gazipur_sreepur_road', 'road_type' => 'Zila Road', 'division' => 'Dhaka', 'district' => 'Gazipur'],

  // ── Narayanganj ──
  ['name' => 'Dhaka–Demra Road', 'code' => 'dhaka_demra_road', 'road_type' => 'Urban Arterial Road', 'division' => 'Dhaka', 'district' => 'Narayanganj'],
  ['name' => 'Narayanganj–Munshiganj Road', 'code' => 'narayanganj_munshiganj_road', 'road_type' => 'Zila Road', 'division' => 'Dhaka', 'district' => 'Narayanganj'],

  // ── Chattogram Division ──
  ['name' => 'CDA Avenue', 'code' => 'cda_avenue', 'road_type' => 'Commercial Corridor', 'division' => 'Chittagong', 'district' => 'Chittagong'],
  ['name' => 'Airport Road Chattogram', 'code' => 'airport_road_chattogram', 'road_type' => 'Airport Access Road', 'division' => 'Chittagong', 'district' => 'Chittagong'],
  ['name' => 'Port Connecting Road', 'code' => 'port_connecting_road', 'road_type' => 'Port Access Road', 'division' => 'Chittagong', 'district' => 'Chittagong'],
  ['name' => 'GEC Circle Road', 'code' => 'gec_circle_road', 'road_type' => 'Commercial Corridor', 'division' => 'Chittagong', 'district' => 'Chittagong'],
  ['name' => 'Agrabad Commercial Road', 'code' => 'agrabad_commercial_road', 'road_type' => 'Commercial Corridor', 'division' => 'Chittagong', 'district' => 'Chittagong'],
  ['name' => 'Oxygen Road', 'code' => 'oxygen_road', 'road_type' => 'Urban Arterial Road', 'division' => 'Chittagong', 'district' => 'Chittagong'],
  ['name' => 'Halishahar Road', 'code' => 'halishahar_road', 'road_type' => 'City Road', 'division' => 'Chittagong', 'district' => 'Chittagong'],
  ['name' => 'Chittagong–Cox\'s Bazar Highway', 'code' => 'chittagong_coxs_bazar_highway', 'road_type' => 'National Highway', 'division' => 'Chittagong', 'district' => 'Chittagong'],
  ['name' => 'Patenga Road', 'code' => 'patenga_road', 'road_type' => 'Port Access Road', 'division' => 'Chittagong', 'district' => 'Chittagong'],

  // ── Comilla ──
  ['name' => 'Comilla Bypass Road', 'code' => 'comilla_bypass_road', 'road_type' => 'Bypass Road', 'division' => 'Chittagong', 'district' => 'Comilla'],
  ['name' => 'Comilla–Chandpur Road', 'code' => 'comilla_chandpur_road', 'road_type' => 'Regional Highway', 'division' => 'Chittagong', 'district' => 'Comilla'],

  // ── Cox's Bazar ──
  ['name' => 'Marine Drive Road', 'code' => 'marine_drive_road', 'road_type' => 'Regional Highway', 'division' => 'Chittagong', 'district' => "Cox's Bazar"],
  ['name' => 'Kolatoli Road', 'code' => 'kolatoli_road', 'road_type' => 'Commercial Corridor', 'division' => 'Chittagong', 'district' => "Cox's Bazar"],

  // ── Rajshahi Division ──
  ['name' => 'Greater Road Rajshahi', 'code' => 'greater_road_rajshahi', 'road_type' => 'Urban Arterial Road', 'division' => 'Rajshahi', 'district' => 'Rajshahi'],
  ['name' => 'Airport Road Rajshahi', 'code' => 'airport_road_rajshahi', 'road_type' => 'Airport Access Road', 'division' => 'Rajshahi', 'district' => 'Rajshahi'],
  ['name' => 'Rajshahi–Chapainawabganj Highway', 'code' => 'rajshahi_chapai_highway', 'road_type' => 'Regional Highway', 'division' => 'Rajshahi', 'district' => 'Rajshahi'],
  ['name' => 'Rajshahi–Natore Highway', 'code' => 'rajshahi_natore_highway', 'road_type' => 'Regional Highway', 'division' => 'Rajshahi', 'district' => 'Rajshahi'],

  // ── Bogra ──
  ['name' => 'Bogra Bypass Road', 'code' => 'bogra_bypass_road', 'road_type' => 'Bypass Road', 'division' => 'Rajshahi', 'district' => 'Bogra'],
  ['name' => 'Rangpur–Bogra Highway', 'code' => 'rangpur_bogra_highway', 'road_type' => 'National Highway', 'division' => 'Rajshahi', 'district' => 'Bogra'],

  // ── Khulna Division ──
  ['name' => 'Khulna–Jessore Highway', 'code' => 'khulna_jessore_highway', 'road_type' => 'National Highway', 'division' => 'Khulna', 'district' => 'Khulna'],
  ['name' => 'Khan Jahan Ali Road', 'code' => 'khan_jahan_ali_road', 'road_type' => 'Commercial Corridor', 'division' => 'Khulna', 'district' => 'Khulna'],
  ['name' => 'Sonadanga Road', 'code' => 'sonadanga_road', 'road_type' => 'Commercial Corridor', 'division' => 'Khulna', 'district' => 'Khulna'],
  ['name' => 'Daulatpur Industrial Road', 'code' => 'daulatpur_industrial_road', 'road_type' => 'Industrial Access Road', 'division' => 'Khulna', 'district' => 'Khulna'],

  // ── Jessore ──
  ['name' => 'Jessore–Benapole Highway', 'code' => 'jessore_benapole_highway', 'road_type' => 'National Highway', 'division' => 'Khulna', 'district' => 'Jessore'],
  ['name' => 'Jessore Town Road', 'code' => 'jessore_town_road', 'road_type' => 'City Road', 'division' => 'Khulna', 'district' => 'Jessore'],

  // ── Sylhet Division ──
  ['name' => 'Sylhet–Tamabil Road', 'code' => 'sylhet_tamabil_road', 'road_type' => 'Regional Highway', 'division' => 'Sylhet', 'district' => 'Sylhet'],
  ['name' => 'Amberkhana Road', 'code' => 'amberkhana_road', 'road_type' => 'Commercial Corridor', 'division' => 'Sylhet', 'district' => 'Sylhet'],
  ['name' => 'Zindabazar Road', 'code' => 'zindabazar_road', 'road_type' => 'Commercial Corridor', 'division' => 'Sylhet', 'district' => 'Sylhet'],
  ['name' => 'Sylhet Airport Road', 'code' => 'sylhet_airport_road', 'road_type' => 'Airport Access Road', 'division' => 'Sylhet', 'district' => 'Sylhet'],

  // ── Barisal Division ──
  ['name' => 'Sadar Road Barishal', 'code' => 'sadar_road_barishal', 'road_type' => 'Commercial Corridor', 'division' => 'Barisal', 'district' => 'Barisal'],
  ['name' => 'Barishal–Patuakhali Highway', 'code' => 'barishal_patuakhali_highway', 'road_type' => 'Regional Highway', 'division' => 'Barisal', 'district' => 'Barisal'],
  ['name' => 'Launch Ghat Road', 'code' => 'launch_ghat_road', 'road_type' => 'City Road', 'division' => 'Barisal', 'district' => 'Barisal'],

  // ── Rangpur Division ──
  ['name' => 'Rangpur Station Road', 'code' => 'rangpur_station_road', 'road_type' => 'City Road', 'division' => 'Rangpur', 'district' => 'Rangpur'],
  ['name' => 'Rangpur–Dinajpur Highway', 'code' => 'rangpur_dinajpur_highway', 'road_type' => 'National Highway', 'division' => 'Rangpur', 'district' => 'Rangpur'],
  ['name' => 'Modern More Road', 'code' => 'modern_more_road', 'road_type' => 'Commercial Corridor', 'division' => 'Rangpur', 'district' => 'Rangpur'],

  // ── Dinajpur ──
  ['name' => 'Dinajpur–Thakurgaon Road', 'code' => 'dinajpur_thakurgaon_road', 'road_type' => 'Regional Highway', 'division' => 'Rangpur', 'district' => 'Dinajpur'],

  // ── Mymensingh Division ──
  ['name' => 'Mymensingh–Kishoreganj Road', 'code' => 'mymensingh_kishoreganj_road', 'road_type' => 'Regional Highway', 'division' => 'Mymensingh', 'district' => 'Mymensingh'],
  ['name' => 'Mymensingh Town Road', 'code' => 'mymensingh_town_road', 'road_type' => 'City Road', 'division' => 'Mymensingh', 'district' => 'Mymensingh'],
];

$created = 0;
$existing = 0;
$failed = 0;

echo "Creating Road Name terms...\n\n";

foreach ($road_names as $data) {
  // Check if term already exists.
  $existing_terms = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term')
    ->loadByProperties([
      'vid' => 'road_name',
      'name' => $data['name'],
    ]);

  if (!empty($existing_terms)) {
    $existing++;
    echo "  Exists: {$data['name']}\n";
    continue;
  }

  // Build term fields.
  $term_data = [
    'vid' => 'road_name',
    'name' => $data['name'],
    'field_road_code' => $data['code'],
  ];

  // Reference: road_type.
  $road_type_tid = find_term_id('road_type', $data['road_type']);
  if ($road_type_tid) {
    $term_data['field_road_type'] = ['target_id' => $road_type_tid];
  }

  // Reference: division.
  $division_tid = find_term_id('division', $data['division']);
  if ($division_tid) {
    $term_data['field_division'] = ['target_id' => $division_tid];
  }

  // Reference: district.
  $district_tid = find_term_id('district', $data['district']);
  if ($district_tid) {
    $term_data['field_district'] = ['target_id' => $district_tid];
  }

  try {
    $term = Term::create($term_data);
    $term->save();
    $created++;
    echo "✓ Created: {$data['name']} ({$data['code']}) — {$data['road_type']}, {$data['division']}/{$data['district']}\n";
  }
  catch (\Exception $e) {
    $failed++;
    echo "✗ Failed: {$data['name']} — {$e->getMessage()}\n";
  }
}

// Summary.
echo "\n" . str_repeat('=', 50) . "\n";
echo "ROAD NAME IMPORT SUMMARY:\n";
echo "Created: $created terms\n";
echo "Already existed: $existing terms\n";
echo "Failed: $failed terms\n";
echo "Total processed: " . count($road_names) . " terms\n";
echo "\n✓ Road name import complete!\n";
