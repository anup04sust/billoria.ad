<?php

/**
 * @file
 * Creates image styles for Billboard images.
 *
 * Run: ddev drush scr scripts/create-billboard-image-styles.php
 */

use Drupal\image\Entity\ImageStyle;

echo "Creating Billboard image styles...\n\n";

$styles = [
  'billboard_hero_large' => [
    'label' => 'Billboard Hero Large (1200×800)',
    'width' => 1200,
    'height' => 800,
    'description' => 'Full page billboard view',
  ],
  'billboard_hero_medium' => [
    'label' => 'Billboard Hero Medium (800×600)',
    'width' => 800,
    'height' => 600,
    'description' => 'Billboard listings, search results',
  ],
  'billboard_hero_thumbnail' => [
    'label' => 'Billboard Hero Thumbnail (400×300)',
    'width' => 400,
    'height' => 300,
    'description' => 'Card view, related billboards',
  ],
  'billboard_gallery_large' => [
    'label' => 'Billboard Gallery Large (1000×750)',
    'width' => 1000,
    'height' => 750,
    'description' => 'Gallery lightbox view',
  ],
  'billboard_gallery_thumbnail' => [
    'label' => 'Billboard Gallery Thumbnail (300×225)',
    'width' => 300,
    'height' => 225,
    'description' => 'Gallery grid thumbnails',
  ],
  'billboard_card_image' => [
    'label' => 'Billboard Card Image (600×400)',
    'width' => 600,
    'height' => 400,
    'description' => 'Card components',
  ],
  'billboard_map_marker' => [
    'label' => 'Billboard Map Marker (150×150)',
    'width' => 150,
    'height' => 150,
    'description' => 'Map popup images',
  ],
];

$created = 0;
$skipped = 0;

foreach ($styles as $name => $config) {
  // Check if style already exists.
  $existing_style = ImageStyle::load($name);

  if ($existing_style) {
    echo "⊘ Skipped: {$config['label']} (already exists)\n";
    $skipped++;
    continue;
  }

  // Create the image style.
  $style = ImageStyle::create([
    'name' => $name,
    'label' => $config['label'],
  ]);

  // Add scale and crop effect.
  $style->addImageEffect([
    'id' => 'image_scale_and_crop',
    'weight' => 0,
    'data' => [
      'width' => $config['width'],
      'height' => $config['height'],
      'anchor' => 'center-center',
    ],
  ]);

  $style->save();

  echo "✓ Created: {$config['label']} ({$config['width']}×{$config['height']})\n";
  echo "  Usage: {$config['description']}\n";
  $created++;
}

echo "\n";
echo "✅ Image styles configuration complete!\n";
echo "Created: $created\n";
echo "Skipped: $skipped\n";
echo "\nImage styles can be found at: /admin/config/media/image-styles\n";
echo "\nNext steps:\n";
echo "1. Update ApiHelper to return image URLs\n";
echo "2. Install image optimization: ddev composer require drupal/imageapi_optimize\n";
echo "3. Configure responsive images for field displays\n";
