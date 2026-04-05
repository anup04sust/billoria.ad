<?php

/**
 * @file
 * Configure billboard image fields with focal point and image optimization.
 *
 * Run: ddev drush scr scripts/configure-billboard-image-features.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

echo "Configuring billboard image features...\n\n";

// ===== Configure Form Display with Focal Point =====
echo "1. Updating form display for hero image...\n";

$form_display = EntityFormDisplay::load('node.billboard.default');
if ($form_display) {
  // Update hero image to use focal_point widget.
  $form_display->setComponent('field_hero_image', [
    'type' => 'image_focal_point',
    'weight' => 75,
    'region' => 'content',
    'settings' => [
      'progress_indicator' => 'throbber',
      'preview_image_style' => 'billboard_hero_medium',
      'preview_link' => TRUE,
      'offsets' => '50,50',
    ],
  ]);

  // Gallery images can also use focal point.
  $form_display->setComponent('field_gallery', [
    'type' => 'image_focal_point',
    'weight' => 76,
    'region' => 'content',
    'settings' => [
      'progress_indicator' => 'throbber',
      'preview_image_style' => 'billboard_gallery_thumbnail',
      'preview_link' => TRUE,
      'offsets' => '50,50',
    ],
  ]);

  $form_display->save();
  echo "  ✓ Form display updated with focal point widgets\n";
}

// ===== Configure View Display =====
echo "\n2. Updating view displays for images...\n";

// Full view mode.
$display_full = EntityViewDisplay::load('node.billboard.default');
if ($display_full) {
  $display_full->setComponent('field_hero_image', [
    'type' => 'image',
    'weight' => 0,
    'label' => 'hidden',
    'settings' => [
      'image_style' => 'billboard_hero_large',
      'image_link' => '',
    ],
  ]);

  $display_full->setComponent('field_gallery', [
    'type' => 'image',
    'weight' => 50,
    'label' => 'above',
    'settings' => [
      'image_style' => 'billboard_gallery_large',
      'image_link' => 'file',
    ],
  ]);

  $display_full->save();
  echo "  ✓ Default (full) view updated\n";
}

// Teaser view mode.
$display_teaser = EntityViewDisplay::load('node.billboard.teaser');
if ($display_teaser) {
  $display_teaser->setComponent('field_hero_image', [
    'type' => 'image',
    'weight' => 0,
    'label' => 'hidden',
    'settings' => [
      'image_style' => 'billboard_hero_medium',
      'image_link' => 'content',
    ],
  ]);

  $display_teaser->save();
  echo "  ✓ Teaser view updated\n";
}

// Card view mode.
$display_card = EntityViewDisplay::load('node.billboard.card');
if ($display_card) {
  $display_card->setComponent('field_hero_image', [
    'type' => 'image',
    'weight' => 0,
    'label' => 'hidden',
    'settings' => [
      'image_style' => 'billboard_card_image',
      'image_link' => 'content',
    ],
  ]);

  $display_card->save();
  echo "  ✓ Card view updated\n";
}

// Map marker view mode.
$display_map = EntityViewDisplay::load('node.billboard.map_marker');
if ($display_map) {
  $display_map->setComponent('field_hero_image', [
    'type' => 'image',
    'weight' => 0,
    'label' => 'hidden',
    'settings' => [
      'image_style' => 'billboard_map_marker',
      'image_link' => 'content',
    ],
  ]);

  $display_map->save();
  echo "  ✓ Map marker view updated\n";
}

// Search result view mode.
$display_search = EntityViewDisplay::load('node.billboard.search_result');
if ($display_search) {
  $display_search->setComponent('field_hero_image', [
    'type' => 'image',
    'weight' => 0,
    'label' => 'hidden',
    'settings' => [
      'image_style' => 'billboard_hero_thumbnail',
      'image_link' => 'content',
    ],
  ]);

  $display_search->save();
  echo "  ✓ Search result view updated\n";
}

echo "\n✅ Billboard image features configured successfully!\n";
echo "\nFeatures enabled:\n";
echo "- Focal point selection for hero and gallery images\n";
echo "- Automatic image style generation (7 styles)\n";
echo "- Responsive image display in different view modes\n";
echo "- Preview images during upload\n";
echo "\nNext steps:\n";
echo "1. Upload a test billboard image at: /node/add/billboard\n";
echo "2. Test focal point selection\n";
echo "3. Verify API returns image URLs: /api/v1/billboard/list\n";
echo "4. Configure imageapi_optimize pipelines at: /admin/config/media/imageapi-optimize\n";
