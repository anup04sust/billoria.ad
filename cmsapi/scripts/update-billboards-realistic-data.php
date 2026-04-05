<?php

/**
 * Update billboards with:
 * - Hero images matching their media type
 * - Realistic Bangladesh coordinates (urban traffic, highways, markets)
 * - Realistic titles, road names, dimensions
 * - Random video source assignment (v1, v2, v3)
 */

use Drupal\file\Entity\File;

$storage = \Drupal::entityTypeManager()->getStorage('node');
$file_storage = \Drupal::entityTypeManager()->getStorage('file');
$db = \Drupal::database();

// ─── 1. Build managed file map: media_format_name → fid ─────────────────────
$image_map = [
  'Bridge Banner'              => 'BridgeBanner.png',
  'Building Wrap'              => 'BuildingWrap.png',
  'Digital Billboard'          => 'DigitalBillboard.png',
  'Directional Signage'        => 'DirectionalSignage.png',
  'Foot Over Bridge Panel'     => 'FootOverBridgePanel.png',
  'Gantry Billboard'           => 'GantryBillboard.png',
  'Lamppost Branding'          => 'LamppostBranding.png',
  'LED Screen'                 => 'LEDScreen.png',
  'Median Panel'               => 'MedianPanel.png',
  'Pole Sign'                  => 'PoleSign.png',
  'Rooftop Sign'               => 'RooftopSign.png',
  'Static Billboard'           => 'StaticBillboard.png',
  'Transit Shelter Branding'   => 'TransitShelterBranding.png',
  'Unipole'                    => 'Unipole.png',
  'Wall Sign'                  => 'WallSign.png',
];

$fid_map = [];
foreach ($image_map as $format_name => $filename) {
  $uri = 'public://billboards/media-types/' . $filename;
  // Check if file entity already exists
  $existing = $file_storage->loadByProperties(['uri' => $uri]);
  if ($existing) {
    $file = reset($existing);
  } else {
    $file = File::create([
      'uri'    => $uri,
      'status' => 1,
      'uid'    => 1,
    ]);
    $file->save();
  }
  $fid_map[$format_name] = $file->id();
  echo "  File: $format_name → fid " . $file->id() . PHP_EOL;
}

// ─── 2. Realistic Bangladesh locations ──────────────────────────────────────
// [lat, lng, area_label, road_name, city]
$locations = [
  // ── Dhaka urban / traffic ──
  ['lat' => 23.7960, 'lng' => 90.4156, 'area' => 'Gulshan 2',        'road' => 'Gulshan Avenue',          'city' => 'Dhaka'],
  ['lat' => 23.7840, 'lng' => 90.4132, 'area' => 'Gulshan 1',        'road' => 'Gulshan Circle Road',     'city' => 'Dhaka'],
  ['lat' => 23.7941, 'lng' => 90.4028, 'area' => 'Banani',           'road' => 'Kamal Ataturk Avenue',    'city' => 'Dhaka'],
  ['lat' => 23.7937, 'lng' => 90.4066, 'area' => 'Banani',           'road' => 'Banani Road 11',          'city' => 'Dhaka'],
  ['lat' => 23.7461, 'lng' => 90.3742, 'area' => 'Dhanmondi',        'road' => 'Dhanmondi 27 Road',       'city' => 'Dhaka'],
  ['lat' => 23.7401, 'lng' => 90.3762, 'area' => 'Dhanmondi',        'road' => 'Mirpur Road',             'city' => 'Dhaka'],
  ['lat' => 23.7577, 'lng' => 90.3880, 'area' => 'Farmgate',         'road' => 'Kazi Nazrul Islam Ave',   'city' => 'Dhaka'],
  ['lat' => 23.7507, 'lng' => 90.3918, 'area' => 'Karwan Bazar',     'road' => 'Tejgaon Industrial Road', 'city' => 'Dhaka'],
  ['lat' => 23.7237, 'lng' => 90.4185, 'area' => 'Motijheel',        'road' => 'Dilkusha Commercial Area','city' => 'Dhaka'],
  ['lat' => 23.7357, 'lng' => 90.4250, 'area' => 'Shantinagar',      'road' => 'Inner Circular Road',     'city' => 'Dhaka'],
  ['lat' => 23.7806, 'lng' => 90.4024, 'area' => 'Mohakhali',        'road' => 'Tejgaon–Gulshan Link Rd', 'city' => 'Dhaka'],
  ['lat' => 23.7703, 'lng' => 90.3981, 'area' => 'Tejgaon',          'road' => 'Airport Road',            'city' => 'Dhaka'],
  ['lat' => 23.8103, 'lng' => 90.3688, 'area' => 'Mirpur 10',        'road' => 'Mirpur Section 10 Road',  'city' => 'Dhaka'],
  ['lat' => 23.7991, 'lng' => 90.3611, 'area' => 'Mirpur 1',         'road' => 'Begum Rokeya Sarani',     'city' => 'Dhaka'],
  ['lat' => 23.8740, 'lng' => 90.3990, 'area' => 'Uttara',           'road' => 'Uttara Sector 4 Road',    'city' => 'Dhaka'],
  ['lat' => 23.8545, 'lng' => 90.3957, 'area' => 'Airport Road',     'road' => 'Hazrat Shahjalal Ave',    'city' => 'Dhaka'],
  ['lat' => 23.8943, 'lng' => 90.4475, 'area' => 'Tongi',            'road' => 'Dhaka–Mymensingh Hwy',    'city' => 'Gazipur'],
  ['lat' => 23.9109, 'lng' => 90.4393, 'area' => 'Gazipur Chowrasta','road' => 'Gazipur Bypass Road',     'city' => 'Gazipur'],
  ['lat' => 24.0024, 'lng' => 90.3944, 'area' => 'Joydevpur',        'road' => 'Dhaka–Tangail Highway',   'city' => 'Gazipur'],
  ['lat' => 23.7302, 'lng' => 90.3735, 'area' => 'Nilkhet',          'road' => 'Elephant Road',           'city' => 'Dhaka'],
  ['lat' => 23.7502, 'lng' => 90.4265, 'area' => 'Malibagh',         'road' => 'DIT Road',                'city' => 'Dhaka'],
  ['lat' => 23.7810, 'lng' => 90.4358, 'area' => 'Badda',            'road' => 'Pragati Sarani',          'city' => 'Dhaka'],
  ['lat' => 23.8003, 'lng' => 90.4245, 'area' => 'Rampura',          'road' => 'Rampura Bridge Road',     'city' => 'Dhaka'],
  ['lat' => 23.7580, 'lng' => 90.4371, 'area' => 'Bashabo',          'road' => 'Demra Road',              'city' => 'Dhaka'],
  // ── Chittagong ──
  ['lat' => 22.3569, 'lng' => 91.8328, 'area' => 'GEC Circle',       'road' => 'CDA Avenue',              'city' => 'Chittagong'],
  ['lat' => 22.3304, 'lng' => 91.8202, 'area' => 'Agrabad',          'road' => 'Sheikh Mujib Road',       'city' => 'Chittagong'],
  ['lat' => 22.3769, 'lng' => 91.7942, 'area' => 'Nasirabad',        'road' => 'Nasirabad Housing Road',  'city' => 'Chittagong'],
  ['lat' => 22.3649, 'lng' => 91.8122, 'area' => 'Muradpur',         'road' => 'Muradpur Intersection',   'city' => 'Chittagong'],
  ['lat' => 22.4079, 'lng' => 91.8032, 'area' => 'Oxygen',           'road' => 'Chittagong–Dhaka Hwy',    'city' => 'Chittagong'],
  ['lat' => 22.3472, 'lng' => 91.8282, 'area' => 'Chawkbazar',       'road' => 'Andarkilla Road',         'city' => 'Chittagong'],
  // ── Sylhet ──
  ['lat' => 24.8949, 'lng' => 91.8687, 'area' => 'Zindabazar',       'road' => 'Zindabazar Main Road',    'city' => 'Sylhet'],
  ['lat' => 24.9046, 'lng' => 91.8557, 'area' => 'Ambarkhana',       'road' => 'Ambarkhana Point',        'city' => 'Sylhet'],
  ['lat' => 24.8978, 'lng' => 91.8762, 'area' => 'Chowhatta',        'road' => 'Chowhatta Road',          'city' => 'Sylhet'],
  ['lat' => 24.9641, 'lng' => 91.8718, 'area' => 'Airport Road',     'road' => 'Sylhet–Dhaka Highway',    'city' => 'Sylhet'],
  // ── Khulna ──
  ['lat' => 22.8010, 'lng' => 89.5539, 'area' => 'KDA Avenue',       'road' => 'Khan-A-Sabur Road',       'city' => 'Khulna'],
  ['lat' => 22.8453, 'lng' => 89.5402, 'area' => 'Boyra',            'road' => 'Khulna–Jessore Highway',  'city' => 'Khulna'],
  ['lat' => 22.8176, 'lng' => 89.5398, 'area' => 'Shonadanga',       'road' => 'Sonadanga Main Road',     'city' => 'Khulna'],
  // ── Rajshahi ──
  ['lat' => 24.3745, 'lng' => 88.6042, 'area' => 'Saheb Bazar',      'road' => 'Saheb Bazar Road',        'city' => 'Rajshahi'],
  ['lat' => 24.3891, 'lng' => 88.5893, 'area' => 'Uposhohor',        'road' => 'Rajshahi–Dhaka Highway',  'city' => 'Rajshahi'],
  ['lat' => 24.3635, 'lng' => 88.6132, 'area' => 'Kazla',            'road' => 'Kazla Bridge Road',       'city' => 'Rajshahi'],
  // ── Comilla ──
  ['lat' => 23.4587, 'lng' => 91.1745, 'area' => 'Kandirpar',        'road' => 'Kandirpar Main Road',     'city' => 'Comilla'],
  ['lat' => 23.4337, 'lng' => 91.1109, 'area' => 'Mainam',           'road' => 'Dhaka–Chittagong Hwy',    'city' => 'Comilla'],
  // ── Narayanganj ──
  ['lat' => 23.6143, 'lng' => 90.4995, 'area' => 'Chashara',         'road' => 'Chashara Bridge Road',    'city' => 'Narayanganj'],
  ['lat' => 23.6272, 'lng' => 90.5133, 'area' => 'Siddhirganj',      'road' => 'Demra–Narayanganj Road',  'city' => 'Narayanganj'],
  // ── Barisal ──
  ['lat' => 22.7052, 'lng' => 90.3696, 'area' => 'Nathullabad',      'road' => 'Barisal–Faridpur Road',   'city' => 'Barisal'],
  ['lat' => 22.6890, 'lng' => 90.3896, 'area' => 'Rupatoli',         'road' => 'Rupatoli Bus Stand Road', 'city' => 'Barisal'],
  // ── Cox's Bazar ──
  ['lat' => 21.4252, 'lng' => 92.0148, 'area' => 'Kolatoli',         'road' => 'Marine Drive',            'city' => "Cox's Bazar"],
  ['lat' => 21.4362, 'lng' => 91.9918, 'area' => 'Sugandha Point',   'road' => 'Cox\'s Bazar Beach Road', 'city' => "Cox's Bazar"],
  // ── Mymensingh ──
  ['lat' => 24.7441, 'lng' => 90.4213, 'area' => 'Ganginarpar',      'road' => 'Mymensingh–Dhaka Road',   'city' => 'Mymensingh'],
  ['lat' => 24.7531, 'lng' => 90.4103, 'area' => 'Town Hall',        'road' => 'Station Road',            'city' => 'Mymensingh'],
  // ── Rangpur ──
  ['lat' => 25.7510, 'lng' => 89.2551, 'area' => 'Modern More',      'road' => 'Rangpur–Dhaka Highway',   'city' => 'Rangpur'],
  ['lat' => 25.7459, 'lng' => 89.2500, 'area' => 'Shapla Chattar',   'road' => 'Jail Road',               'city' => 'Rangpur'],
];

// ─── 3. Realistic dimensions per format ────────────────────────────────────
$dimensions_map = [
  'Static Billboard'           => ['40', '20', '40\'×20\''],
  'Digital Billboard'          => ['30', '15', '30\'×15\''],
  'LED Screen'                 => ['20', '12', '20\'×12\''],
  'Unipole'                    => ['48', '24', '48\'×24\''],
  'Gantry Billboard'           => ['60', '15', '60\'×15\''],
  'Bridge Banner'              => ['30', '8',  '30\'×8\''],
  'Building Wrap'              => ['50', '80', '50\'×80\''],
  'Rooftop Sign'               => ['24', '12', '24\'×12\''],
  'Wall Sign'                  => ['20', '10', '20\'×10\''],
  'Pole Sign'                  => ['12', '8',  '12\'×8\''],
  'Median Panel'               => ['8',  '4',  '8\'×4\''],
  'Foot Over Bridge Panel'     => ['20', '6',  '20\'×6\''],
  'Lamppost Branding'          => ['4',  '6',  '4\'×6\''],
  'Transit Shelter Branding'   => ['6',  '4',  '6\'×4\''],
  'Directional Signage'        => ['10', '4',  '10\'×4\''],
];

// ─── 4. Video sources ───────────────────────────────────────────────────────
$videos = [
  '/videos/optimized/v1-wm.mp4',
  '/videos/optimized/v2-wm.mp4',
  '/videos/optimized/v3-wm.mp4',
];

// ─── 5. Update each billboard node ─────────────────────────────────────────
$nodes = $storage->loadByProperties(['type' => 'billboard']);
$loc_count = count($locations);
$i = 0;

foreach ($nodes as $node) {
  $nid = $node->id();

  // Get media format name
  $format_name = 'Static Billboard';
  if ($node->get('field_media_format')->entity) {
    $format_name = $node->get('field_media_format')->entity->getName();
  }

  // Assign location cyclically
  $loc = $locations[$i % $loc_count];
  $i++;

  // Assign image fid
  $fid = $fid_map[$format_name] ?? $fid_map['Static Billboard'];

  // Assign dimensions
  $dim = $dimensions_map[$format_name] ?? ['20', '10', '20\'×10\''];

  // Assign random video
  $video = $videos[$nid % 3];

  // Build realistic title
  $title = $format_name . ' — ' . $loc['area'] . ', ' . $loc['city'];

  // Update fields
  $node->set('title', $title);
  $node->set('field_latitude', number_format((float)$loc['lat'], 7, '.', ''));
  $node->set('field_longitude', number_format((float)$loc['lng'], 7, '.', ''));
  $node->set('field_width_ft', $dim[0]);
  $node->set('field_height_ft', $dim[1]);
  $node->set('field_display_size', $dim[2]);

  // Set hero image
  $node->set('field_hero_image', [
    'target_id' => $fid,
    'alt'       => $format_name . ' at ' . $loc['area'],
    'title'     => $title,
  ]);

  // Set video source if field exists
  if ($node->hasField('field_video_source')) {
    $node->set('field_video_source', $video);
  }

  $node->save();
  echo "✓ NID $nid | $format_name | {$loc['area']}, {$loc['city']} | fid=$fid | $video" . PHP_EOL;
}

echo PHP_EOL . '✅ All billboards updated!' . PHP_EOL;
