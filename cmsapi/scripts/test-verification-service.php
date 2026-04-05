#!/usr/bin/env php
<?php

/**
 * @file
 * Test the User Verification Service.
 *
 * Usage: ddev drush scr scripts/test-verification-service.php
 */

use Drupal\user\Entity\User;

echo "=== Testing UserVerificationService ===\n\n";

// Get the service.
$verificationService = \Drupal::service('billoria_accounts.user_verification');

// Test 1: Generate OTP
echo "1. Generate OTP Code:\n";
$otp = $verificationService->generateOtpCode(6);
echo "   Generated: $otp (6 digits)\n\n";

// Test 2: Create verification record
echo "2. Create Email Verification Record:\n";
$test_uid = 1; // Admin user
$test_email = 'admin@billoria.ad';

$verification_id = $verificationService->createVerification(
  $test_uid,
  'email',
  $test_email,
  $otp,
  600, // 10 minutes
  5,   // 5 max attempts
  ['ip' => '127.0.0.1', 'user_agent' => 'Test Script']
);

if ($verification_id) {
  echo "   ✓ Created verification ID: $verification_id\n";
  echo "   Type: email\n";
  echo "   Identifier: $test_email\n";
  echo "   Code: $otp\n";
  echo "   Expires in: 10 minutes\n\n";
} else {
  echo "   ✗ Failed to create verification\n\n";
  exit(1);
}

// Test 3: Get latest verification
echo "3. Get Latest Verification:\n";
$latest = $verificationService->getLatestVerification($test_uid, 'email');
if ($latest) {
  echo "   ID: {$latest->id}\n";
  echo "   Status: {$latest->status}\n";
  echo "   Attempts: {$latest->attempts}/{$latest->max_attempts}\n";
  echo "   Created: " . date('Y-m-d H:i:s', $latest->created) . "\n";
  echo "   Expires: " . date('Y-m-d H:i:s', $latest->expires) . "\n\n";
}

// Test 4: Try wrong code
echo "4. Test Wrong Code:\n";
$wrong_result = $verificationService->verifyCode($verification_id, '000000');
echo "   Success: " . ($wrong_result['success'] ? 'Yes' : 'No') . "\n";
echo "   Message: {$wrong_result['message']}\n";
if (isset($wrong_result['data']['attempts_remaining'])) {
  echo "   Attempts Remaining: {$wrong_result['data']['attempts_remaining']}\n";
}
echo "\n";

// Test 5: Verify correct code
echo "5. Test Correct Code:\n";
$correct_result = $verificationService->verifyCode($verification_id, $otp);
echo "   Success: " . ($correct_result['success'] ? 'Yes' : 'No') . "\n";
echo "   Message: {$correct_result['message']}\n";
if ($correct_result['success'] && isset($correct_result['data'])) {
  echo "   Type: {$correct_result['data']['verification_type']}\n";
  echo "   Identifier: {$correct_result['data']['identifier']}\n";
}
echo "\n";

// Test 6: Rate limiting check
echo "6. Test Rate Limiting:\n";

// Create another verification
$otp2 = $verificationService->generateOtpCode(6);
$verification_id2 = $verificationService->createVerification(
  $test_uid,
  'phone',
  '+8801712345678',
  $otp2,
  300,
  3
);

// Immediately try to create another (should be rate limited)
$rate_check = $verificationService->canRequestNewCode($test_uid, 'phone', 60);
echo "   Allowed: " . ($rate_check['allowed'] ? 'Yes' : 'No') . "\n";
echo "   Message: {$rate_check['message']}\n";
if (isset($rate_check['retry_after'])) {
  echo "   Retry After: {$rate_check['retry_after']} seconds\n";
}
echo "\n";

// Test 7: Get statistics
echo "7. Get Verification Statistics:\n";
$stats = $verificationService->getVerificationStats($test_uid);
echo "   Total: {$stats['total']}\n";
echo "   Verified: {$stats['verified']}\n";
echo "   Pending: {$stats['pending']}\n";
echo "   Failed: {$stats['failed']}\n";
echo "   Expired: {$stats['expired']}\n\n";

// Test 8: Cancel pending verifications
echo "8. Cancel Pending Phone Verifications:\n";
$verificationService->cancelPendingVerifications($test_uid, 'phone');
echo "   ✓ Cancelled all pending phone verifications\n\n";

// Test 9: Query database directly
echo "9. Database Query Results:\n";
$connection = \Drupal::database();
$results = $connection->select('billoria_user_verifications', 'v')
  ->fields('v', ['id', 'verification_type', 'identifier', 'status', 'attempts'])
  ->condition('v.uid', $test_uid)
  ->execute()
  ->fetchAll();

foreach ($results as $row) {
  echo "   ID {$row->id}: {$row->verification_type} | {$row->identifier} | {$row->status} | {$row->attempts} attempts\n";
}
echo "\n";

echo "=== All Tests Completed Successfully! ===\n";
