#!/usr/bin/env php
<?php

/**
 * @file
 * Install billoria_user_verifications database table.
 *
 * Usage: ddev drush scr scripts/install-verification-table.php
 */

use Drupal\Core\Database\Database;

// Get the database connection.
$connection = Database::getConnection();
$schema = $connection->schema();

// Define the table name.
$table_name = 'billoria_user_verifications';

// Check if table already exists.
if ($schema->tableExists($table_name)) {
  echo "⚠️  Table '{$table_name}' already exists. Dropping and recreating...\n";
  $schema->dropTable($table_name);
}

// Create the table.
$table_schema = [
  'description' => 'Stores verification codes and attempts for user email, phone, and other verification processes.',
  'fields' => [
    'id' => [
      'type' => 'serial',
      'not null' => TRUE,
      'description' => 'Primary Key: Unique verification record ID.',
    ],
    'uid' => [
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'default' => 0,
      'description' => 'The {users}.uid this verification belongs to.',
    ],
    'verification_type' => [
      'type' => 'varchar',
      'length' => 32,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Type of verification: email, phone, sms, etc.',
    ],
    'identifier' => [
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The value being verified (email address, phone number, etc.).',
    ],
    'code' => [
      'type' => 'varchar',
      'length' => 32,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The verification code (OTP, token, etc.).',
    ],
    'code_hash' => [
      'type' => 'varchar',
      'length' => 64,
      'not null' => FALSE,
      'description' => 'Hashed version of the code for secure comparison.',
    ],
    'status' => [
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => 'pending',
      'description' => 'Status: pending, verified, expired, failed, cancelled.',
    ],
    'attempts' => [
      'type' => 'int',
      'size' => 'tiny',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'default' => 0,
      'description' => 'Number of verification attempts made.',
    ],
    'max_attempts' => [
      'type' => 'int',
      'size' => 'tiny',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'default' => 5,
      'description' => 'Maximum number of attempts allowed.',
    ],
    'created' => [
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'description' => 'Timestamp when the verification code was created.',
    ],
    'expires' => [
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'description' => 'Timestamp when the verification code expires.',
    ],
    'verified_at' => [
      'type' => 'int',
      'not null' => FALSE,
      'default' => NULL,
      'description' => 'Timestamp when verification was completed.',
    ],
    'last_attempt_at' => [
      'type' => 'int',
      'not null' => FALSE,
      'default' => NULL,
      'description' => 'Timestamp of the last verification attempt.',
    ],
    'metadata' => [
      'type' => 'text',
      'size' => 'big',
      'not null' => FALSE,
      'description' => 'JSON-encoded metadata (e.g., IP address, user agent, delivery status).',
    ],
  ],
  'primary key' => ['id'],
  'indexes' => [
    'uid' => ['uid'],
    'verification_type' => ['verification_type'],
    'identifier' => ['identifier'],
    'status' => ['status'],
    'created' => ['created'],
    'expires' => ['expires'],
    'uid_type' => ['uid', 'verification_type'],
    'uid_type_status' => ['uid', 'verification_type', 'status'],
  ],
];

$schema->createTable($table_name, $table_schema);

echo "✓ Table '{$table_name}' created successfully!\n";
echo "\n";
echo "Table structure:\n";
echo "- id (serial): Primary key\n";
echo "- uid (int): User ID reference\n";
echo "- verification_type (varchar): email, phone, sms, etc.\n";
echo "- identifier (varchar): Email address, phone number, etc.\n";
echo "- code (varchar): Verification code (OTP)\n";
echo "- code_hash (varchar): SHA-256 hash of code\n";
echo "- status (varchar): pending, verified, expired, failed, cancelled\n";
echo "- attempts (tinyint): Number of attempts made\n";
echo "- max_attempts (tinyint): Maximum attempts allowed\n";
echo "- created (int): Timestamp when created\n";
echo "- expires (int): Timestamp when expires\n";
echo "- verified_at (int): Timestamp when verified\n";
echo "- last_attempt_at (int): Timestamp of last attempt\n";
echo "- metadata (text): JSON metadata\n";
echo "\n";
echo "✓ Ready to use with UserVerificationService!\n";

// Clear cache.
drupal_flush_all_caches();
echo "✓ Cache cleared.\n";
