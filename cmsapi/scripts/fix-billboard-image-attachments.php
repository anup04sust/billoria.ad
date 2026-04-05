<?php

/**
 * Fix: Attach existing billboard image files to nodes
 *
 * The files were created but not attached due to field configuration issue.
 * This script attaches existing files to their respective billboard nodes.
 *
 * Usage: ddev drush scr scripts/fix-billboard-image-attachments.php
 */

use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

echo "\n🔧 Fixing billboard image attachments...\n\n";

// Mapping of billboard IDs to file IDs
$billboardFiles = [
  22 => ['hero' => 1, 'gallery' => [2, 3]],
  23 => ['hero' => 4, 'gallery' => [5, 6, 7]],
  24 => ['hero' => 8, 'gallery' => [9, 10, 11]],
  25 => ['hero' => 12, 'gallery' => [13, 14, 15]],
  26 => ['hero' => 16, 'gallery' => [17, 18, 19]],
];

$updated = 0;
$errors = 0;

foreach ($billboardFiles as $nid => $files) {
  $node = Node::load($nid);
  if (!$node) {
    echo "❌ Billboard $nid not found\n";
    $errors++;
    continue;
  }

  echo "📋 Billboard $nid: " . $node->getTitle() . "\n";

  // Attach hero image
  if (isset($files['hero'])) {
    $heroFile = File::load($files['hero']);
    if ($heroFile) {
      $node->set('field_hero_image', [
        'target_id' => $heroFile->id(),
        'alt' => 'Hero image for ' . $node->getTitle(),
        'title' => 'Main billboard view',
      ]);
      echo "  ✅ Attached hero image (FID {$heroFile->id()})\n";
    } else {
      echo "  ❌ Hero file not found\n";
      $errors++;
    }
  }

  // Attach gallery images
  if (isset($files['gallery']) && !empty($files['gallery'])) {
    $galleryValues = [];
    foreach ($files['gallery'] as $index => $fid) {
      $galleryFile = File::load($fid);
      if ($galleryFile) {
        $galleryValues[] = [
          'target_id' => $galleryFile->id(),
          'alt' => 'Gallery image ' . ($index + 1) . ' for ' . $node->getTitle(),
          'title' => 'Gallery view ' . ($index + 1),
        ];
      }
    }

    if (!empty($galleryValues)) {
      $node->set('field_gallery', $galleryValues);
      echo "  ✅ Attached " . count($galleryValues) . " gallery images\n";
    }
  }

  // Save the node
  try {
    $node->save();
    $updated++;
    echo "  ✅ Saved billboard $nid\n\n";
  } catch (\Exception $e) {
    echo "  ❌ Failed to save: " . $e->getMessage() . "\n\n";
    $errors++;
  }
}

// Summary
echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Fix complete!\n";
echo "   - Billboards updated: $updated\n";
echo "   - Errors: $errors\n";
echo str_repeat('=', 60) . "\n\n";

echo "🎯 Test the API now:\n";
echo "   curl -k -s https://billoria-ad-api.ddev.site/api/v1/billboard/22 | python3 -m json.tool | grep -A 20 hero_image\n\n";
