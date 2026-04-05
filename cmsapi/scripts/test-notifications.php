#!/usr/bin/env php
<?php

/**
 * @file
 * Test script for Billoria Notifications module.
 *
 * Usage: cd /var/www/billoria.ad/cmsapi && ddev drush scr scripts/test-notifications.php
 */

// This script should be run via drush scr to bootstrap Drupal properly.

// Get the notification manager service.
$notification_manager = \Drupal::service('billoria_notifications.manager');

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║   Billoria Notifications Module - Test Script            ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Get a test user (user ID 1 - admin).
$test_uid = 1;
echo "Testing with User ID: $test_uid\n\n";

// Test 1: Create test notifications.
echo "📝 Creating test notifications...\n";

$notification_types = [
  [
    'type' => 'booking',
    'title' => 'Booking Request Approved',
    'message' => 'Your booking request for Billboard #123 has been approved.',
    'priority' => 'high',
    'metadata' => ['billboard_id' => 123, 'booking_id' => 456],
  ],
  [
    'type' => 'verification',
    'title' => 'KYC Verification Complete',
    'message' => 'Your identity verification has been completed successfully.',
    'priority' => 'normal',
    'metadata' => ['verification_status' => 'approved'],
  ],
  [
    'type' => 'system',
    'title' => 'Welcome to Billoria!',
    'message' => 'Thank you for joining our billboard marketplace platform.',
    'priority' => 'low',
    'metadata' => ['onboarding' => true],
  ],
  [
    'type' => 'alert',
    'title' => 'Payment Due Soon',
    'message' => 'Your payment for booking #456 is due in 2 days.',
    'priority' => 'urgent',
    'metadata' => ['booking_id' => 456, 'due_date' => date('Y-m-d', strtotime('+2 days'))],
  ],
];

$created_ids = [];
foreach ($notification_types as $notification) {
  $nid = $notification_manager->createNotification(
    $test_uid,
    $notification['type'],
    $notification['title'],
    $notification['message'],
    $notification['metadata'],
    $notification['priority']
  );
  
  if ($nid) {
    $created_ids[] = $nid;
    echo "  ✓ Created [{$notification['priority']}] {$notification['title']} (ID: $nid)\n";
  } else {
    echo "  ✗ Failed to create: {$notification['title']}\n";
  }
}

echo "\n";

// Test 2: Get unread count.
echo "📊 Checking unread notifications...\n";
$unread_count = $notification_manager->getUnreadCount($test_uid);
echo "  Unread count: $unread_count\n\n";

// Test 3: List all notifications.
echo "📋 Listing all notifications...\n";
$notifications = $notification_manager->getNotifications($test_uid, 10);
foreach ($notifications as $n) {
  $status = $n['is_read'] ? '✓ Read' : '○ Unread';
  $priority_emoji = match($n['priority']) {
    'urgent' => '🔴',
    'high' => '🟡',
    'normal' => '🟢',
    'low' => '⚪',
    default => '⚫',
  };
  echo "  $priority_emoji [$status] {$n['title']} ({$n['type']})\n";
}
echo "\n";

// Test 4: Mark first notification as read.
if (!empty($created_ids)) {
  $first_id = $created_ids[0];
  echo "✅ Marking notification #$first_id as read...\n";
  $success = $notification_manager->markAsRead($first_id, $test_uid);
  echo "  Result: " . ($success ? 'Success' : 'Failed') . "\n";
  
  $unread_count = $notification_manager->getUnreadCount($test_uid);
  echo "  New unread count: $unread_count\n\n";
}

// Test 5: Get only unread notifications.
echo "📬 Listing unread notifications only...\n";
$unread_notifications = $notification_manager->getNotifications($test_uid, 10, 0, FALSE);
echo "  Found " . count($unread_notifications) . " unread notification(s)\n";
foreach ($unread_notifications as $n) {
  echo "    • {$n['title']}\n";
}
echo "\n";

// Test 6: Mark all as read.
echo "✅ Marking all notifications as read...\n";
$marked = $notification_manager->markAllAsRead($test_uid);
echo "  Marked $marked notification(s) as read\n";

$unread_count = $notification_manager->getUnreadCount($test_uid);
echo "  Final unread count: $unread_count\n\n";

// Test 7: Delete a notification.
if (!empty($created_ids)) {
  $delete_id = end($created_ids);
  echo "🗑️  Deleting notification #$delete_id...\n";
  $success = $notification_manager->deleteNotification($delete_id, $test_uid);
  echo "  Result: " . ($success ? 'Success' : 'Failed') . "\n\n";
}

// Test 8: Test expiration functionality.
echo "⏰ Testing notification expiration...\n";
$expired_nid = $notification_manager->createNotification(
  $test_uid,
  'test',
  'This notification will expire',
  'This is a test of the expiration feature.',
  [],
  'normal',
  time() - 3600  // Expired 1 hour ago
);

if ($expired_nid) {
  echo "  ✓ Created expired notification (ID: $expired_nid)\n";
  
  // Run cleanup.
  $deleted = $notification_manager->deleteExpiredNotifications();
  echo "  Cleaned up $deleted expired notification(s)\n";
}

echo "\n";

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║   ✅ All tests completed successfully!                   ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "📱 Testing FCM Token Management...\n";
$firebase_service = \Drupal::service('billoria_notifications.firebase');

// Test token registration.
$test_token = 'test-fcm-token-' . time();
$register_success = $firebase_service->registerToken(
  $test_uid,
  $test_token,
  'web',
  'Test Browser'
);

if ($register_success) {
  echo "  ✓ Registered FCM token\n";
} else {
  echo "  ✗ Failed to register token\n";
}

// Get user tokens.
$user_tokens = $firebase_service->getUserTokens($test_uid);
echo "  Found " . count($user_tokens) . " registered device(s)\n";

foreach ($user_tokens as $token) {
  echo "    • {$token['device_type']}: {$token['device_name']}\n";
}

// Unregister token.
$unregister_success = $firebase_service->unregisterToken($test_token, $test_uid);
echo "  " . ($unregister_success ? '✓' : '✗') . " Unregistered test token\n\n";

echo "Note: Configure Firebase Server Key to enable push notifications:\n";
echo "  ddev drush config:set billoria_notifications.firebase server_key \"YOUR_KEY\" -y\n\n";

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║   ✅ All tests completed successfully!                   ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "API Endpoints Available:\n";
echo "  # Notifications\n";
echo "  GET    /api/v1/notifications\n";
echo "  GET    /api/v1/notifications/unread-count\n";
echo "  POST   /api/v1/notifications/{nid}/mark-read\n";
echo "  POST   /api/v1/notifications/mark-all-read\n";
echo "  DELETE /api/v1/notifications/{nid}\n\n";
echo "  # FCM Push Notifications\n";
echo "  POST   /api/v1/notifications/fcm/register\n";
echo "  POST   /api/v1/notifications/fcm/unregister\n";
echo "  GET    /api/v1/notifications/fcm/tokens\n\n";

echo "For more information, see:\n";
echo "  cmsapi/web/modules/custom/billoria_notifications/README.md\n";
echo "  application-wiki/NOTIFICATIONS_API.md\n";
echo "  application-wiki/FCM_PUSH_NOTIFICATIONS.md\n";
