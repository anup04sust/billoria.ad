#!/usr/bin/env php
<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once __DIR__ . '/../web/autoload.php';
$kernel = new DrupalKernel('prod', $autoloader);
$request = Request::createFromGlobals();
$kernel->boot();
$kernel->preHandle($request);

echo "Testing Alpha SMS API Connection...\n\n";

$sms_sender = \Drupal::service('billoria_sms.sender');

// Check if configured
if (!$sms_sender->isConfigured()) {
  echo "✗ SMS service is not configured\n";
  exit(1);
}

echo "✓ SMS service is configured\n\n";

// Get balance
echo "Checking account balance...\n";
$balance_result = $sms_sender->getBalance();

if ($balance_result['success']) {
  echo "✓ Connection successful!\n";
  echo "  Account Balance: {$balance_result['balance']} BDT\n\n";
} else {
  echo "✗ Connection failed: {$balance_result['message']}\n";
  echo "  Error code: " . ($balance_result['error_code'] ?? 'unknown') . "\n\n";
  exit(1);
}

echo "SMS API is ready to use!\n";
echo "You can now send OTPs for phone verification.\n";
