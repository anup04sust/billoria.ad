<?php

/**
 * @file
 * Script to add verification and organization fields to User entity.
 *
 * This extends the user entity to support:
 * - Email/phone verification
 * - Organization membership
 * - Profile completion tracking
 *
 * Usage: ddev drush php:script scripts/add-user-verification-fields.php
 */

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

echo "=== EXTENDING USER ENTITY WITH VERIFICATION FIELDS ===\n\n";

// Step 1: Create field storage configurations
echo "Step 1: Creating field storage configurations for user entity...\n";

$field_storages = [
  'field_mobile_number' => [
    'type' => 'telephone',
    'cardinality' => 1,
  ],
  'field_designation' => [
    'type' => 'string',
    'cardinality' => 1,
  ],
  'field_department' => [
    'type' => 'string',
    'cardinality' => 1,
  ],
  'field_email_verified' => [
    'type' => 'boolean',
    'cardinality' => 1,
  ],
  'field_phone_verified' => [
    'type' => 'boolean',
    'cardinality' => 1,
  ],
  'field_verification_token' => [
    'type' => 'string',
    'cardinality' => 1,
  ],
  'field_token_expiry' => [
    'type' => 'timestamp',
    'cardinality' => 1,
  ],
  'field_organization' => [
    'type' => 'entity_reference',
    'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    'settings' => [
      'target_type' => 'node',
    ],
  ],
  'field_active_organization' => [
    'type' => 'entity_reference',
    'cardinality' => 1,
    'settings' => [
      'target_type' => 'node',
    ],
  ],
  'field_is_primary_admin' => [
    'type' => 'boolean',
    'cardinality' => 1,
  ],
  'field_secondary_email' => [
    'type' => 'email',
    'cardinality' => 1,
  ],
  'field_phone_otp' => [
    'type' => 'string',
    'cardinality' => 1,
  ],
  'field_phone_otp_expiry' => [
    'type' => 'timestamp',
    'cardinality' => 1,
  ],
];

$created_storages = 0;
$existing_storages = 0;

foreach ($field_storages as $field_name => $config) {
  $field_storage = FieldStorageConfig::loadByName('user', $field_name);

  if ($field_storage) {
    $existing_storages++;
  } else {
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'user',
      'type' => $config['type'],
      'cardinality' => $config['cardinality'],
      'settings' => $config['settings'] ?? [],
    ]);
    $field_storage->save();
    $created_storages++;
    echo "  ✓ Created storage: $field_name\n";
  }
}

echo "\n  Created: $created_storages | Already existed: $existing_storages\n";

// Step 2: Attach fields to user entity
echo "\nStep 2: Attaching fields to user entity...\n";

$field_configs = [
  'field_mobile_number' => [
    'label' => 'Mobile Number',
    'required' => TRUE,
    'description' => 'Your mobile number (required for verification)',
  ],
  'field_designation' => [
    'label' => 'Designation',
    'required' => FALSE,
    'description' => 'Your job title or role',
  ],
  'field_department' => [
    'label' => 'Department',
    'required' => FALSE,
  ],
  'field_email_verified' => [
    'label' => 'Email Verified',
    'required' => FALSE,
    'default_value' => [['value' => 0]],
    'description' => 'Has this user verified their email?',
  ],
  'field_phone_verified' => [
    'label' => 'Phone Verified',
    'required' => FALSE,
    'default_value' => [['value' => 0]],
    'description' => 'Has this user verified their phone?',
  ],
  'field_verification_token' => [
    'label' => 'Email Verification Token',
    'required' => FALSE,
    'description' => 'Token for email verification (1-hour expiry)',
  ],
  'field_token_expiry' => [
    'label' => 'Token Expiry Time',
    'required' => FALSE,
  ],
  'field_organization' => [
    'label' => 'Organizations',
    'required' => FALSE,
    'description' => 'Organizations this user belongs to',
    'settings' => [
      'handler' => 'default:node',
      'handler_settings' => [
        'target_bundles' => ['organization' => 'organization'],
      ],
    ],
  ],
  'field_active_organization' => [
    'label' => 'Active Organization',
    'required' => FALSE,
    'description' => 'Currently active organization context',
    'settings' => [
      'handler' => 'default:node',
      'handler_settings' => [
        'target_bundles' => ['organization' => 'organization'],
      ],
    ],
  ],
  'field_is_primary_admin' => [
    'label' => 'Is Primary Organization Admin',
    'required' => FALSE,
    'default_value' => [['value' => 0]],
  ],
  'field_secondary_email' => [
    'label' => 'Secondary Email',
    'required' => FALSE,
    'description' => 'Backup email address',
  ],
  'field_phone_otp' => [
    'label' => 'Phone OTP',
    'required' => FALSE,
    'description' => 'Current OTP for phone verification',
  ],
  'field_phone_otp_expiry' => [
    'label' => 'Phone OTP Expiry',
    'required' => FALSE,
  ],
];

$created_fields = 0;
$existing_fields = 0;

foreach ($field_configs as $field_name => $config) {
  $field = FieldConfig::loadByName('user', 'user', $field_name);

  if ($field) {
    $existing_fields++;
  } else {
    $field_storage = FieldStorageConfig::loadByName('user', $field_name);
    if ($field_storage) {
      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => 'user',
        'label' => $config['label'],
        'required' => $config['required'] ?? FALSE,
        'description' => $config['description'] ?? '',
        'default_value' => $config['default_value'] ?? NULL,
        'settings' => $config['settings'] ?? [],
      ]);
      $field->save();
      $created_fields++;
      echo "  ✓ Attached field: $field_name\n";
    } else {
      echo "  ✗ Field storage not found for: $field_name\n";
    }
  }
}

echo "\n  Attached: $created_fields | Already attached: $existing_fields\n";

// Step 3: Configure form display
echo "\nStep 3: Configuring user form display...\n";

$form_display = \Drupal::service('entity_display.repository')
  ->getFormDisplay('user', 'user', 'default');

$form_display->setComponent('field_mobile_number', [
  'type' => 'telephone_default',
  'weight' => 5,
]);

$form_display->setComponent('field_designation', [
  'type' => 'string_textfield',
  'weight' => 6,
]);

$form_display->setComponent('field_organization', [
  'type' => 'entity_reference_autocomplete',
  'weight' => 10,
  'settings' => [
    'match_operator' => 'CONTAINS',
    'size' => 60,
    'placeholder' => 'Start typing organization name...',
  ],
]);

$form_display->setComponent('field_active_organization', [
  'type' => 'options_select',
  'weight' => 11,
]);

// Hide internal fields from form
$form_display->removeComponent('field_email_verified');
$form_display->removeComponent('field_phone_verified');
$form_display->removeComponent('field_verification_token');
$form_display->removeComponent('field_token_expiry');
$form_display->removeComponent('field_phone_otp');
$form_display->removeComponent('field_phone_otp_expiry');

$form_display->save();

echo "  ✓ User form display configured\n";

// Step 4: Configure compact view display
echo "\nStep 4: Configuring user view display...\n";

$view_display = \Drupal::service('entity_display.repository')
  ->getViewDisplay('user', 'user', 'compact');

$view_display->setComponent('field_mobile_number', [
  'type' => 'telephone_link',
  'weight' => 1,
  'label' => 'inline',
]);

$view_display->setComponent('field_designation', [
  'type' => 'string',
  'weight' => 2,
  'label' => 'inline',
]);

$view_display->setComponent('field_organization', [
  'type' => 'entity_reference_label',
  'weight' => 3,
  'label' => 'inline',
  'settings' => [
    'link' => TRUE,
  ],
]);

$view_display->save();

echo "  ✓ User view display (compact) configured\n";

echo "\n=== USER ENTITY EXTENDED SUCCESSFULLY ===\n";
echo "\nSummary:\n";
echo "- Field storages created: $created_storages\n";
echo "- Fields attached to user entity: $created_fields\n";
echo "- Form display: configured (public fields visible, internal hidden)\n";
echo "- View display: configured\n";
echo "\nNew user capabilities:\n";
echo "- ✓ Mobile number field (required)\n";
echo "- ✓ Email verification tracking\n";
echo "- ✓ Phone verification tracking\n";
echo "- ✓ Multi-organization membership support\n";
echo "- ✓ Active organization context switching\n";
echo "- ✓ Token-based verification system\n";
echo "\nNext steps:\n";
echo "1. Create custom registration routes and forms\n";
echo "2. Build email verification service\n";
echo "3. Build phone/SMS verification service\n";
echo "\n✓ User entity ready for onboarding flow!\n";
