<?php

/**
 * Add sample placeholder images to existing billboards
 *
 * This script creates simple placeholder images using GD library
 * and attaches them to billboard nodes to test image processing features.
 *
 * Usage: ddev drush scr scripts/add-sample-billboard-images.php
 */

use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Create a simple placeholder image using GD
 */
function createPlaceholderImage($width, $height, $text, $bgColor = [52, 152, 219]) {
  // Create image
  $image = imagecreatetruecolor($width, $height);

  // Allocate colors
  $bg = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
  $white = imagecolorallocate($image, 255, 255, 255);
  $gray = imagecolorallocate($image, 200, 200, 200);

  // Fill background
  imagefill($image, 0, 0, $bg);

  // Add grid pattern
  for ($i = 0; $i < $width; $i += 50) {
    imageline($image, $i, 0, $i, $height, $gray);
  }
  for ($i = 0; $i < $height; $i += 50) {
    imageline($image, 0, $i, $width, $i, $gray);
  }

  // Add text
  $textSize = 5;
  $textWidth = imagefontwidth($textSize) * strlen($text);
  $textHeight = imagefontheight($textSize);
  $x = ($width - $textWidth) / 2;
  $y = ($height - $textHeight) / 2;
  imagestring($image, $textSize, $x, $y, $text, $white);

  // Add dimensions text
  $dimText = "{$width}x{$height}px";
  $dimWidth = imagefontwidth(3) * strlen($dimText);
  imagestring($image, 3, ($width - $dimWidth) / 2, $y + 30, $dimText, $white);

  // Output to buffer
  ob_start();
  imagejpeg($image, NULL, 85);
  $imageData = ob_get_clean();
  imagedestroy($image);

  return $imageData;
}

/**
 * Save placeholder image as Drupal file
 */
function savePlaceholderImage($imageData, $filename, $alt = '', $title = '') {
  try {
    // Create directory if it doesn't exist
    $directory = 'public://billboard-images';
    \Drupal::service('file_system')->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

    // Save file
    $file = \Drupal::service('file.repository')->writeData($imageData, "$directory/$filename", \Drupal\Core\File\FileSystemInterface::EXISTS_REPLACE);

    if ($file) {
      echo "  ✅ Created: $filename (" . number_format(strlen($imageData) / 1024, 1) . " KB)\n";
    }

    return NULL;
  } catch (\Exception $e) {
    echo "  ❌ Error: " . $e->getMessage() . "\n";
    return NULL;
  }
}

// Color schemes for variety
$colors = [
  [52, 152, 219],   // Blue
  [46, 204, 113],   // Green
  [155, 89, 182],   // Purple
  [230, 126, 34],   // Orange
  [231, 76, 60],    // Red
];

// Get all billboard nodes
$nids = \Drupal::entityQuery('node')
  ->condition('type', 'billboard')
  ->accessCheck(FALSE)
  ->execute();

if (empty($nids)) {
  echo "❌ No billboard nodes found!\n";
  exit(1);
}

echo "\n🖼️  Adding sample images to " . count($nids) . " billboards...\n\n";

$updated = 0;
$errors = 0;
$colorIndex = 0;

foreach ($nids as $nid) {
  $node = Node::load($nid);
  if (!$node) {
    continue;
  }

  echo "📋 Billboard $nid: " . $node->getTitle() . "\n";

  // Create and attach hero image
  echo "  Creating hero image...\n";
  $heroImageData = createPlaceholderImage(
    1600,
    1200,
    "Billboard #{$nid} - Hero Image",
    $colors[$colorIndex % count($colors)]
  );

  $heroImage = savePlaceholderImage(
    $heroImageData,
    "billboard-{$nid}-hero.jpg",
    "Hero image for " . $node->getTitle(),
    "Main billboard view"
  );

  if ($heroImage) {
    $node->set('field_hero_image', $heroImage);
  } else {
    $errors++;
  }

  // Create and attach 2-3 gallery images
  $galleryFiles = [];
  $numGalleryImages = rand(2, 3);

  echo "  Creating $numGalleryImages gallery images...\n";
  for ($i = 0; $i < $numGalleryImages; $i++) {
    $galleryColorIndex = ($colorIndex + $i + 1) % count($colors);
    $galleryImageData = createPlaceholderImage(
      1200,
      900,
      "Billboard #{$nid} - Gallery " . ($i + 1),
      $colors[$galleryColorIndex]
    );

    $galleryImage = savePlaceholderImage(
      $galleryImageData,
      "billboard-{$nid}-gallery-{$i}.jpg",
      "Gallery image " . ($i + 1) . " for " . $node->getTitle()
    );

    if ($galleryImage) {
      $galleryFiles[] = $galleryImage;
    } else {
      $errors++;
    }
  }

  if (!empty($galleryFiles)) {
    $node->set('field_gallery', $galleryFiles);
  }

  // Save the node
  try {
    $node->save();
    $updated++;
    echo "  ✅ Updated billboard $nid with images\n\n";
  } catch (\Exception $e) {
    echo "  ❌ Failed to save: " . $e->getMessage() . "\n\n";
    $errors++;
  }

  $colorIndex++;
}

// Summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Summary:\n";
echo "   - Billboards updated: $updated\n";
echo "   - Errors: $errors\n";
echo "   - Total billboards: " . count($nids) . "\n";
echo str_repeat('=', 60) . "\n\n";

echo "🎯 Next steps:\n";
echo "   1. Test API: curl https://billoria-ad-api.ddev.site/api/v1/billboard/list\n";
echo "   2. View billboard: https://billoria.ad.ddev.site/node/22\n";
echo "   3. Check image styles: /admin/config/media/image-styles\n\n";
