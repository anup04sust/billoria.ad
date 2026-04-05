#!/usr/bin/env php
<?php

/**
 * Configure Alpha SMS API settings.
 * 
 * Usage:
 *   php scripts/configure-sms.php YOUR_API_KEY [SENDER_ID]
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once __DIR__ . '/../web/autoload.php';
$kernel = new DrupalKernel('prod', $autoloader);
$request = Request::createFromGlobals();
$kernel->boot();
$kernel->preHandle($request);

// Get arguments
$api_key = $argv[1] ?? NULL;
$sender_id = $argv[2] ?? NULL;

if (empty($api_key)) {
  echo "Usage: php scripts/configure-sms.php YOUR_API_KEY [SENDER_ID]\n";
  echo "\nExample:\n";
  echo "  php scripts/configure-sms.php vlekNzB1FfeGLi60g1G826ZfOss6MVj41S5V5Ex0 Billoria\n";
  exit(1);
}

// Save configuration
$config = \Drupal::configFactory()->getEditable('billoria_sms.settings');
$config->set('api_key', $api_key);

if (!empty($sender_id)) {
  $config->set('sender_id', $sender_id);
}

$config->save();

echo "✓ SMS API configuration saved\n";
echo "  API Key: " . substr($api_key, 0, 10) . "..." . substr($api_key, -10) . "\n";

if (!empty($sender_id)) {
  echo "  Sender ID: $sender_id\n";
}

// Test configuration
$sms_sender = \Drupal::service('billoria_sms.sender');
$balance_result = $sms_sender->getBalance();

if ($balance_result['success']) {
  echo "\n✓ Connection successful!\n";
  echo "  Account Balance: {$balance_result['balance']} BDT\n";
}
else {
  echo "\n✗ Connection failed: {$balance_result['message']}\n";
  exit(1);
}

echo "\nConfiguration complete! You can now use the SMS service.\n";
echo "Admin UI: http://billoria-ad-api.ddev.site/admin/config/billoria/sms\n";
