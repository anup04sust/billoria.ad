<?php

/**
 * Configure verification documents status field allowed values
 */

use Drupal\field\Entity\FieldStorageConfig;

// Get the field storage
$field_storage = FieldStorageConfig::loadByName('node', 'field_verification_docs_status');
if ($field_storage) {
  // Set allowed values
  $allowed_values = [
    'pending_review' => 'Pending Review',
    'verified' => 'Verified',
    'rejected' => 'Rejected',
  ];

  $field_storage->setSetting('allowed_values', $allowed_values);
  $field_storage->save();

  echo "Field storage allowed values updated successfully.\n";
} else {
  echo "Field storage not found.\n";
}

// Clear cache
\Drupal::service('cache_tags.invalidator')->invalidateTags(['config:field.storage.node.field_verification_docs_status']);
echo "Cache cleared.\n";