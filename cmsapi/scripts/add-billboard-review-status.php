<?php

/**
 * @file
 * Add field_review_status (List text) to billboard content type.
 *
 * Statuses: draft, pending_review, approved, revision_requested, rejected.
 *
 * Usage: ddev drush scr scripts/add-billboard-review-status.php
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

$field_name = 'field_review_status';
$entity_type = 'node';
$bundle = 'billboard';

$allowed_values = [
  'draft' => 'Draft',
  'pending_review' => 'Pending Review',
  'approved' => 'Approved',
  'revision_requested' => 'Revision Requested',
  'rejected' => 'Rejected',
];

// Create field storage if it doesn't exist.
if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
  FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => $entity_type,
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => $allowed_values,
    ],
  ])->save();
  echo "✅ Field storage '$field_name' created.\n";
}
else {
  echo "ℹ️  Field storage '$field_name' already exists.\n";
}

// Create field instance on billboard bundle.
if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
  FieldConfig::create([
    'field_name' => $field_name,
    'entity_type' => $entity_type,
    'bundle' => $bundle,
    'label' => 'Review Status',
    'description' => 'Content review status for billboard listings.',
    'required' => FALSE,
    'default_value' => [['value' => 'draft']],
  ])->save();
  echo "✅ Field instance '$field_name' added to '$bundle'.\n";
}
else {
  echo "ℹ️  Field instance '$field_name' already exists on '$bundle'.\n";
}

// Add to form display.
$form_display = EntityFormDisplay::load("$entity_type.$bundle.default");
if ($form_display && !$form_display->getComponent($field_name)) {
  $form_display->setComponent($field_name, [
    'type' => 'options_select',
    'weight' => 5,
  ])->save();
  echo "✅ Added '$field_name' to form display.\n";
}

// Add to view display.
$view_display = EntityViewDisplay::load("$entity_type.$bundle.default");
if ($view_display && !$view_display->getComponent($field_name)) {
  $view_display->setComponent($field_name, [
    'type' => 'list_default',
    'weight' => 5,
    'label' => 'above',
  ])->save();
  echo "✅ Added '$field_name' to view display.\n";
}

echo "\n🎉 Done! Billboard review statuses: " . implode(', ', $allowed_values) . "\n";
