#!/usr/bin/env php
<?php

/**
 * @file
 * Test script to send a push notification.
 *
 * Usage:
 *   ddev ssh
 *   php scripts/test-push-notification.php [user_id] [title] [message]
 *
 * Examples:
 *   php scripts/test-push-notification.php 1 "Welcome!" "Welcome to Billoria.ad"
 *   php scripts/test-push-notification.php 1
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Bootstrap Drupal.
$autoloader = require_once __DIR__ . '/../vendor/autoload.php';
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$kernel->preHandle($request);
\Drupal::setContainer($kernel->getContainer());

// Get command line arguments.
$uid = $argv[1] ?? 1; // Default to admin user
$title = $argv[2] ?? 'Welcome to Billoria.ad! 🎉';
$message = $argv[3] ?? 'Thank you for joining our billboard marketplace platform. Start exploring billboards in your area or list your own!';

// Get the notification manager service.
$notificationManager = \Drupal::service('billoria_notifications.manager');

echo "Sending test notification...\n";
echo "----------------------------\n";
echo "User ID: $uid\n";
echo "Title: $title\n";
echo "Message: $message\n";
echo "----------------------------\n\n";

// Create notification with push.
$nid = $notificationManager->createNotification(
  uid: (int) $uid,
  type: 'welcome',
  title: $title,
  message: $message,
  metadata: [
    'action' => 'explore',
    'url' => '/',
  ],
  priority: 'normal',
  expires_at: NULL,
  send_push: TRUE
);

if ($nid) {
  echo "✅ Success!\n";
  echo "Notification ID: $nid\n";
  echo "Push notification sent to all registered devices for user $uid\n";
  
  // Get user's registered tokens.
  $firebaseService = \Drupal::service('billoria_notifications.firebase');
  $tokens = $firebaseService->getUserTokens((int) $uid);
  echo "Devices notified: " . count($tokens) . "\n";
  
  if (count($tokens) > 0) {
    echo "\nRegistered device types:\n";
    foreach ($tokens as $token_data) {
      echo "  - {$token_data['device_type']}\n";
    }
  } else {
    echo "\n⚠️  Warning: User has no registered FCM tokens.\n";
    echo "To receive push notifications:\n";
    echo "1. Login to the frontend (https://billoria-ad.ddev.site:3001)\n";
    echo "2. Allow notification permissions when prompted\n";
    echo "3. Run this script again\n";
  }
} else {
  echo "❌ Failed to create notification\n";
  exit(1);
}

echo "\n";
