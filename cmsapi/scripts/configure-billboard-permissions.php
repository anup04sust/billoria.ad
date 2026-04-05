<?php

/**
 * @file
 * Set up permissions for Billboard content type.
 *
 * Run: ddev drush scr scripts/configure-billboard-permissions.php
 */

use Drupal\user\Entity\Role;

echo "Configuring Billboard permissions...\n\n";

// Get roles
$roles = [
  'administrator' => Role::load('administrator'),
  'authenticated' => Role::load('authenticated'),
];

// Check if billoria_core roles exist
$billoria_roles = ['billboard_owner', 'brand_user', 'agency_user'];
foreach ($billoria_roles as $role_id) {
  $role = Role::load($role_id);
  if ($role) {
    $roles[$role_id] = $role;
  }
}

// Billboard permissions to grant
$permissions = [
  'administrator' => [
    'create billboard content',
    'edit own billboard content',
    'edit any billboard content',
    'delete own billboard content',
    'delete any billboard content',
    'view billboard revisions',
    'revert billboard revisions',
    'delete billboard revisions',
  ],
  'billboard_owner' => [
    'create billboard content',
    'edit own billboard content',
    'delete own billboard content',
    'view own unpublished content',
  ],
  'brand_user' => [
    'view published content',  // Can view billboards for booking
  ],
  'agency_user' => [
    'view published content',  // Can view billboards for booking
  ],
  'authenticated' => [
    // No billboard creation/edit permissions for regular authenticated users
  ],
];

foreach ($permissions as $role_id => $perms) {
  if (isset($roles[$role_id])) {
    $role = $roles[$role_id];
    foreach ($perms as $permission) {
      if (!$role->hasPermission($permission)) {
        $role->grantPermission($permission);
        echo "✓ Granted '$permission' to $role_id\n";
      }
    }
    $role->save();
  }
}

echo "\n✅ Billboard permissions configured!\n";
echo "\nPermission summary:\n";
echo "- Administrator: Full control (create, edit any, delete any)\n";
echo "- Billboard Owner: Create and manage own billboards\n";
echo "- Brand User: View published billboards only\n";
echo "- Agency User: View published billboards only\n";
echo "\nNote: Access control will also check field_owner_organization\n";
