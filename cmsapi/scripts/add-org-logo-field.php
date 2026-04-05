<?php

/**
 * @file
 * Adds field_org_logo (image) field to the organization content type.
 *
 * Usage: ddev drush php:script scripts/add-org-logo-field.php
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

echo "=== ADDING LOGO FIELD TO ORGANIZATION CONTENT TYPE ===\n\n";

// Step 1: Create field storage if it doesn't exist.
echo "Step 1: Creating field_org_logo storage...\n";

$storage = FieldStorageConfig::loadByName('node', 'field_org_logo');
if ($storage) {
  echo "  field_org_logo storage already exists. Skipping.\n";
} else {
  $storage = FieldStorageConfig::create([
    'field_name'  => 'field_org_logo',
    'entity_type' => 'node',
    'type'        => 'image',
    'cardinality' => 1,
    'settings'    => [
      'uri_scheme'     => 'public',
      'default_image'  => [
        'uuid'   => '',
        'alt'    => '',
        'title'  => '',
        'width'  => NULL,
        'height' => NULL,
      ],
    ],
  ]);
  $storage->save();
  echo "  ✓ Created field_org_logo storage.\n";
}

// Step 2: Attach field to the organization content type.
echo "\nStep 2: Attaching field_org_logo to organization content type...\n";

$field = FieldConfig::loadByName('node', 'organization', 'field_org_logo');
if ($field) {
  echo "  field_org_logo already attached to organization. Skipping.\n";
} else {
  $field = FieldConfig::create([
    'field_storage' => $storage,
    'bundle'        => 'organization',
    'label'         => 'Organization Logo',
    'required'      => FALSE,
    'settings'      => [
      'file_extensions' => 'png jpg jpeg webp svg',
      'file_directory'  => 'org-logos/[date:custom:Y-m]',
      'max_filesize'    => '2 MB',
      'alt_field'       => TRUE,
      'alt_field_required' => FALSE,
      'title_field'     => FALSE,
      'min_resolution'  => '',
      'max_resolution'  => '1200x1200',
      'default_image'   => [
        'uuid'   => '',
        'alt'    => '',
        'title'  => '',
        'width'  => NULL,
        'height' => NULL,
      ],
    ],
  ]);
  $field->save();
  echo "  ✓ Attached field_org_logo to organization content type.\n";
}

// Step 3: Add to form display.
echo "\nStep 3: Updating form display...\n";

$form_display = EntityFormDisplay::load('node.organization.default');
if (!$form_display) {
  echo "  ⚠ Default form display not found. Skipping form display update.\n";
} else {
  $form_display->setComponent('field_org_logo', [
    'type'     => 'image_image',
    'weight'   => 5,
    'settings' => [
      'preview_image_style' => 'thumbnail',
      'progress_indicator'  => 'throbber',
    ],
  ]);
  $form_display->save();
  echo "  ✓ Added field_org_logo to default form display.\n";
}

// Step 4: Add to view display.
echo "\nStep 4: Updating view display...\n";

$view_display = EntityViewDisplay::load('node.organization.default');
if (!$view_display) {
  echo "  ⚠ Default view display not found. Skipping view display update.\n";
} else {
  $view_display->setComponent('field_org_logo', [
    'type'     => 'image',
    'weight'   => 5,
    'label'    => 'hidden',
    'settings' => [
      'image_style' => 'thumbnail',
      'image_link'  => '',
    ],
  ]);
  $view_display->save();
  echo "  ✓ Added field_org_logo to default view display.\n";
}

// Step 5: Clear caches.
echo "\nStep 5: Clearing caches...\n";
drupal_flush_all_caches();
echo "  ✓ Caches cleared.\n";

echo "\n=== DONE ===\n";
echo "field_org_logo image field added to organization content type.\n";
echo "File uploads accepted: png, jpg, jpeg, webp, svg (max 2MB).\n";
echo "Files stored at: public://org-logos/[year-month]/\n";
echo "\nRun via: ddev drush php:script scripts/add-org-logo-field.php\n";
