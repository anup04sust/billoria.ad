<?php

/**
 * @file
 * Script to create Organization content type with all fields.
 *
 * This creates the foundation for Brand, Agency, and Owner organization profiles.
 *
 * Usage: ddev drush php:script scripts/create-organization-content-type.php
 */

use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

echo "=== CREATING ORGANIZATION CONTENT TYPE ===\n\n";

// Step 1: Create Organization content type
echo "Step 1: Creating Organization content type...\n";

$organization_type = NodeType::load('organization');
if ($organization_type) {
  echo "  Organization content type already exists. Skipping creation.\n";
} else {
  $organization_type = NodeType::create([
    'type' => 'organization',
    'name' => 'Organization',
    'description' => 'Brand, Agency, or Billboard Owner organization profile',
    'title_label' => 'Organization Name',
    'display_submitted' => FALSE,
    'new_revision' => TRUE,
  ]);
  $organization_type->save();
  echo "  ✓ Created Organization content type\n";
}

// Step 2: Create field storage configurations
echo "\nStep 2: Creating field storage configurations...\n";

$field_storages = [
  // Organization type (brand/agency/owner)
  'field_org_type' => [
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'brand' => 'Brand/Advertiser',
        'agency' => 'Advertising Agency',
        'owner' => 'Billboard Owner',
      ],
    ],
  ],

  // Contact information
  'field_official_email' => [
    'type' => 'email',
    'cardinality' => 1,
  ],
  'field_official_phone' => [
    'type' => 'telephone',
    'cardinality' => 1,
  ],
  'field_mobile_banking' => [
    'type' => 'telephone',
    'cardinality' => 1,
  ],
  'field_website' => [
    'type' => 'link',
    'cardinality' => 1,
  ],

  // Team references
  'field_primary_admin' => [
    'type' => 'entity_reference',
    'cardinality' => 1,
    'settings' => [
      'target_type' => 'user',
    ],
  ],
  'field_team_members' => [
    'type' => 'entity_reference',
    'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    'settings' => [
      'target_type' => 'user',
    ],
  ],

  // Business information
  'field_business_reg_number' => [
    'type' => 'string',
    'cardinality' => 1,
  ],
  'field_tin' => [
    'type' => 'string',
    'cardinality' => 1,
  ],
  'field_establishment_year' => [
    'type' => 'integer',
    'cardinality' => 1,
  ],

  // Verification & trust
  'field_verification_status' => [
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'draft' => 'Draft',
        'email_verified' => 'Email Verified',
        'pending' => 'Pending Review',
        'verified' => 'Business Verified',
        'suspended' => 'Suspended',
      ],
    ],
  ],
  'field_trust_score' => [
    'type' => 'integer',
    'cardinality' => 1,
  ],
  'field_profile_completion' => [
    'type' => 'integer',
    'cardinality' => 1,
  ],

  // Assets
  'field_org_logo' => [
    'type' => 'image',
    'cardinality' => 1,
  ],
  'field_verification_docs' => [
    'type' => 'file',
    'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
  ],

  // Location references
  'field_division' => [
    'type' => 'entity_reference',
    'cardinality' => 1,
    'settings' => [
      'target_type' => 'taxonomy_term',
    ],
  ],
  'field_district' => [
    'type' => 'entity_reference',
    'cardinality' => 1,
    'settings' => [
      'target_type' => 'taxonomy_term',
    ],
  ],
  'field_city_corporation' => [
    'type' => 'entity_reference',
    'cardinality' => 1,
    'settings' => [
      'target_type' => 'taxonomy_term',
    ],
  ],
  'field_full_address' => [
    'type' => 'string_long',
    'cardinality' => 1,
  ],
  'field_postal_code' => [
    'type' => 'string',
    'cardinality' => 1,
  ],

  // Brand-specific fields
  'field_industry_category' => [
    'type' => 'entity_reference',
    'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    'settings' => [
      'target_type' => 'taxonomy_term',
    ],
  ],
  'field_parent_company' => [
    'type' => 'string',
    'cardinality' => 1,
  ],
  'field_annual_budget_range' => [
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'under_5l' => 'Under 5 Lakhs',
        '5l_20l' => '5-20 Lakhs',
        '20l_50l' => '20-50 Lakhs',
        '50l_1cr' => '50 Lakhs - 1 Crore',
        'over_1cr' => 'Over 1 Crore',
      ],
    ],
  ],
  'field_preferred_regions' => [
    'type' => 'entity_reference',
    'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    'settings' => [
      'target_type' => 'taxonomy_term',
    ],
  ],
  'field_booking_duration' => [
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'short_term' => 'Short-term (1-3 months)',
        'seasonal' => 'Seasonal (3-6 months)',
        'annual' => 'Annual (6-12 months)',
        'long_term' => 'Long-term (12+ months)',
      ],
    ],
  ],

  // Agency-specific fields
  'field_agency_services' => [
    'type' => 'list_string',
    'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    'settings' => [
      'allowed_values' => [
        'media_planning' => 'Media Planning',
        'creative' => 'Creative Design',
        'ooh' => 'Out-of-Home (OOH)',
        'digital' => 'Digital Marketing',
        'btl' => 'Below The Line (BTL)',
        'events' => 'Events & Activations',
      ],
    ],
  ],
  'field_portfolio_size' => [
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'small' => 'Small (1-10 clients)',
        'medium' => 'Medium (10-50 clients)',
        'large' => 'Large (50+ clients)',
      ],
    ],
  ],
  'field_owns_inventory' => [
    'type' => 'boolean',
    'cardinality' => 1,
  ],
  'field_operations_contact' => [
    'type' => 'string',
    'cardinality' => 1,
  ],
  'field_finance_contact' => [
    'type' => 'string',
    'cardinality' => 1,
  ],

  // Owner-specific fields
  'field_inventory_count' => [
    'type' => 'integer',
    'cardinality' => 1,
  ],
  'field_total_coverage_sqft' => [
    'type' => 'decimal',
    'cardinality' => 1,
    'settings' => [
      'precision' => 10,
      'scale' => 2,
    ],
  ],
  'field_maintenance_capability' => [
    'type' => 'list_string',
    'cardinality' => 1,
    'settings' => [
      'allowed_values' => [
        'own_team' => 'Own Team',
        'outsourced' => 'Outsourced',
        'both' => 'Both',
      ],
    ],
  ],
  'field_installation_services' => [
    'type' => 'boolean',
    'cardinality' => 1,
  ],
  'field_coverage_districts' => [
    'type' => 'entity_reference',
    'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    'settings' => [
      'target_type' => 'taxonomy_term',
    ],
  ],
];

$created_storages = 0;
$existing_storages = 0;

foreach ($field_storages as $field_name => $config) {
  $field_storage = FieldStorageConfig::loadByName('node', $field_name);

  if ($field_storage) {
    $existing_storages++;
  } else {
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
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

// Step 3: Attach fields to Organization content type
echo "\nStep 3: Attaching fields to Organization content type...\n";

$field_configs = [
  'field_org_type' => [
    'label' => 'Organization Type',
    'required' => TRUE,
    'description' => 'Select your organization type',
  ],
  'field_official_email' => [
    'label' => 'Official Email',
    'required' => TRUE,
    'description' => 'Primary business email address',
  ],
  'field_official_phone' => [
    'label' => 'Official Phone',
    'required' => TRUE,
    'description' => 'Primary business phone number',
  ],
  'field_mobile_banking' => [
    'label' => 'Mobile Banking Number',
    'required' => FALSE,
    'description' => 'bKash/Nagad number for transactions',
  ],
  'field_website' => [
    'label' => 'Website',
    'required' => FALSE,
  ],
  'field_primary_admin' => [
    'label' => 'Primary Administrator',
    'required' => TRUE,
    'description' => 'Organization owner/primary contact',
    'settings' => [
      'handler' => 'default:user',
    ],
  ],
  'field_team_members' => [
    'label' => 'Team Members',
    'required' => FALSE,
    'description' => 'Additional users with access to this organization',
    'settings' => [
      'handler' => 'default:user',
    ],
  ],
  'field_business_reg_number' => [
    'label' => 'Business Registration Number',
    'required' => FALSE,
    'description' => 'Trade License or Company Registration Number',
  ],
  'field_tin' => [
    'label' => 'TIN (Tax Identification Number)',
    'required' => FALSE,
  ],
  'field_establishment_year' => [
    'label' => 'Year Established',
    'required' => FALSE,
  ],
  'field_verification_status' => [
    'label' => 'Verification Status',
    'required' => TRUE,
    'default_value' => [['value' => 'draft']],
  ],
  'field_trust_score' => [
    'label' => 'Trust Score',
    'required' => FALSE,
    'default_value' => [['value' => 50]],
    'description' => 'Auto-calculated trust score (0-100)',
  ],
  'field_profile_completion' => [
    'label' => 'Profile Completion %',
    'required' => FALSE,
    'default_value' => [['value' => 0]],
    'description' => 'Auto-calculated completion percentage',
  ],
  'field_org_logo' => [
    'label' => 'Organization Logo',
    'required' => FALSE,
    'settings' => [
      'file_directory' => 'organization-logos/[date:custom:Y]-[date:custom:m]',
      'max_filesize' => '2 MB',
      'file_extensions' => 'png jpg jpeg',
      'alt_field' => TRUE,
      'alt_field_required' => FALSE,
    ],
  ],
  'field_verification_docs' => [
    'label' => 'Verification Documents',
    'required' => FALSE,
    'description' => 'Upload ownership/business documents (private)',
    'settings' => [
      'file_directory' => 'verification-docs/[date:custom:Y]-[date:custom:m]',
      'max_filesize' => '5 MB',
      'file_extensions' => 'pdf jpg jpeg png',
      'uri_scheme' => 'private',
    ],
  ],
  'field_division' => [
    'label' => 'Division',
    'required' => TRUE,
    'settings' => [
      'handler' => 'default:taxonomy_term',
      'handler_settings' => [
        'target_bundles' => ['division' => 'division'],
      ],
    ],
  ],
  'field_district' => [
    'label' => 'District',
    'required' => TRUE,
    'settings' => [
      'handler' => 'default:taxonomy_term',
      'handler_settings' => [
        'target_bundles' => ['district' => 'district'],
      ],
    ],
  ],
  'field_city_corporation' => [
    'label' => 'City Corporation',
    'required' => FALSE,
    'settings' => [
      'handler' => 'default:taxonomy_term',
      'handler_settings' => [
        'target_bundles' => ['city_corporation' => 'city_corporation'],
      ],
    ],
  ],
  'field_full_address' => [
    'label' => 'Full Address',
    'required' => TRUE,
  ],
  'field_postal_code' => [
    'label' => 'Postal Code',
    'required' => FALSE,
  ],

  // Brand-specific
  'field_industry_category' => [
    'label' => 'Industry Category',
    'required' => FALSE,
    'description' => 'For brands: select your industry',
  ],
  'field_parent_company' => [
    'label' => 'Parent Company',
    'required' => FALSE,
    'description' => 'If you are a subsidiary',
  ],
  'field_annual_budget_range' => [
    'label' => 'Annual Marketing Budget Range',
    'required' => FALSE,
  ],
  'field_preferred_regions' => [
    'label' => 'Preferred Advertising Regions',
    'required' => FALSE,
    'settings' => [
      'handler' => 'default:taxonomy_term',
      'handler_settings' => [
        'target_bundles' => ['division' => 'division'],
      ],
    ],
  ],
  'field_booking_duration' => [
    'label' => 'Preferred Booking Duration',
    'required' => FALSE,
  ],

  // Agency-specific
  'field_agency_services' => [
    'label' => 'Services Offered',
    'required' => FALSE,
  ],
  'field_portfolio_size' => [
    'label' => 'Client Portfolio Size',
    'required' => FALSE,
  ],
  'field_owns_inventory' => [
    'label' => 'Do you own billboard inventory?',
    'required' => FALSE,
  ],
  'field_operations_contact' => [
    'label' => 'Operations Contact',
    'required' => FALSE,
  ],
  'field_finance_contact' => [
    'label' => 'Finance Contact',
    'required' => FALSE,
  ],

  // Owner-specific
  'field_inventory_count' => [
    'label' => 'Number of Billboards',
    'required' => FALSE,
    'description' => 'Approximate billboard inventory count',
  ],
  'field_total_coverage_sqft' => [
    'label' => 'Total Coverage Area (sq ft)',
    'required' => FALSE,
  ],
  'field_maintenance_capability' => [
    'label' => 'Maintenance Capability',
    'required' => FALSE,
  ],
  'field_installation_services' => [
    'label' => 'Offer Installation Services',
    'required' => FALSE,
  ],
  'field_coverage_districts' => [
    'label' => 'Coverage Districts',
    'required' => FALSE,
    'description' => 'Districts where you have billboard inventory',
    'settings' => [
      'handler' => 'default:taxonomy_term',
      'handler_settings' => [
        'target_bundles' => ['district' => 'district'],
      ],
    ],
  ],
];

$created_fields = 0;
$existing_fields = 0;

foreach ($field_configs as $field_name => $config) {
  $field = FieldConfig::loadByName('node', 'organization', $field_name);

  if ($field) {
    $existing_fields++;
  } else {
    $field_storage = FieldStorageConfig::loadByName('node', $field_name);
    if ($field_storage) {
      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => 'organization',
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

// Step 4: Configure form display
echo "\nStep 4: Configuring form display...\n";

$form_display = \Drupal::service('entity_display.repository')
  ->getFormDisplay('node', 'organization', 'default');

// Common fields
$form_display->setComponent('field_org_type', [
  'type' => 'options_select',
  'weight' => -10,
]);

$form_display->setComponent('field_official_email', [
  'type' => 'email_default',
  'weight' => 0,
]);

$form_display->setComponent('field_official_phone', [
  'type' => 'telephone_default',
  'weight' => 1,
]);

$form_display->setComponent('field_website', [
  'type' => 'link_default',
  'weight' => 2,
]);

// Location fields
$form_display->setComponent('field_division', [
  'type' => 'options_select',
  'weight' => 10,
]);

$form_display->setComponent('field_district', [
  'type' => 'options_select',
  'weight' => 11,
]);

$form_display->setComponent('field_full_address', [
  'type' => 'string_textarea',
  'weight' => 12,
]);

// Business fields
$form_display->setComponent('field_business_reg_number', [
  'type' => 'string_textfield',
  'weight' => 20,
]);

$form_display->setComponent('field_tin', [
  'type' => 'string_textfield',
  'weight' => 21,
]);

// Assets
$form_display->setComponent('field_org_logo', [
  'type' => 'image_image',
  'weight' => 30,
]);

$form_display->setComponent('field_verification_docs', [
  'type' => 'file_generic',
  'weight' => 31,
]);

$form_display->save();

echo "  ✓ Form display configured\n";

// Step 5: Configure view display
echo "\nStep 5: Configuring view display...\n";

$view_display = \Drupal::service('entity_display.repository')
  ->getViewDisplay('node', 'organization', 'default');

$view_display->setComponent('field_org_type', [
  'type' => 'list_default',
  'weight' => 0,
  'label' => 'inline',
]);

$view_display->setComponent('field_official_email', [
  'type' => 'basic_string',
  'weight' => 1,
  'label' => 'inline',
]);

$view_display->setComponent('field_org_logo', [
  'type' => 'image',
  'weight' => -1,
  'label' => 'hidden',
  'settings' => [
    'image_style' => 'medium',
  ],
]);

$view_display->setComponent('field_verification_status', [
  'type' => 'list_default',
  'weight' => 2,
  'label' => 'inline',
]);

$view_display->setComponent('field_trust_score', [
  'type' => 'number_integer',
  'weight' => 3,
  'label' => 'inline',
]);

$view_display->save();

echo "  ✓ View display configured\n";

echo "\n=== ORGANIZATION CONTENT TYPE CREATED SUCCESSFULLY ===\n";
echo "\nSummary:\n";
echo "- Content type: organization\n";
echo "- Field storages created: $created_storages\n";
echo "- Fields attached: $created_fields\n";
echo "- Form display: configured\n";
echo "- View display: configured\n";
echo "\nNext steps:\n";
echo "1. Configure permissions for organization content type\n";
echo "2. Create registration forms for each organization type\n";
echo "3. Extend user entity with verification fields\n";
echo "\n✓ Ready to create organizations!\n";
