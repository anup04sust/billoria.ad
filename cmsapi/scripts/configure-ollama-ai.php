#!/usr/bin/env php
<?php

/**
 * @file
 * Configure Ollama AI integration for Billoria platform.
 *
 * Usage: php scripts/configure-ollama-ai.php
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Bootstrap Drupal.
$autoloader = require_once 'web/autoload.php';
$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$kernel->prepareLegacyRequest(Request::createFromGlobals());

$config_factory = \Drupal::configFactory();
$module_handler = \Drupal::moduleHandler();
$logger = \Drupal::logger('billoria_ai_setup');

echo "\n╔════════════════════════════════════════╗\n";
echo "║   Billoria Ollama AI Configuration    ║\n";
echo "╚════════════════════════════════════════╝\n\n";

// Step 1: Check if AI modules are enabled
echo "Step 1: Checking AI modules...\n";
$ai_modules = ['ai', 'ai_provider_ollama'];
$modules_to_enable = [];

foreach ($ai_modules as $module) {
  if ($module_handler->moduleExists($module)) {
    echo "  ✓ $module is enabled\n";
  } else {
    echo "  ✗ $module is not enabled\n";
    $modules_to_enable[] = $module;
  }
}

if (!empty($modules_to_enable)) {
  echo "\n❌ Required AI modules are not enabled.\n";
  echo "Run this command to enable them:\n";
  echo "  ddev drush pm:enable " . implode(' ', $modules_to_enable) . " -y\n\n";
  exit(1);
}

// Step 2: Check Ollama service availability
echo "\nStep 2: Checking Ollama service...\n";
$ollama_host = 'localhost';
$ollama_port = '11434';

try {
  $client = \Drupal::httpClient();
  $response = $client->get("http://{$ollama_host}:{$ollama_port}/api/tags", [
    'timeout' => 5,
  ]);

  $body = json_decode($response->getBody()->getContents(), TRUE);
  $models = $body['models'] ?? [];

  echo "  ✓ Ollama service is running on {$ollama_host}:{$ollama_port}\n";

  if (!empty($models)) {
    echo "  ✓ Available models:\n";
    foreach ($models as $model) {
      echo "    - {$model['name']}\n";
    }
  } else {
    echo "  ⚠ No models installed. You need to pull a model.\n";
    echo "    Run: ollama pull llama3.2\n";
  }
} catch (Exception $e) {
  echo "  ✗ Cannot connect to Ollama service\n";
  echo "    Error: " . $e->getMessage() . "\n\n";
  echo "❌ Ollama is not running. Please install and start it:\n";
  echo "  Linux: curl -fsSL https://ollama.com/install.sh | sh\n";
  echo "  macOS: brew install ollama && ollama serve\n";
  echo "  Then pull a model: ollama pull llama3.2\n\n";
  exit(1);
}

// Step 3: Configure Ollama settings
echo "\nStep 3: Configuring Ollama settings...\n";

$config = $config_factory->getEditable('billoria_core.settings');

// Set default Ollama configuration
$config->set('ollama.host', $ollama_host);
$config->set('ollama.port', $ollama_port);
$config->set('ollama.model', 'llama3.2');
$config->set('ollama.timeout', 30);

$config->save();

echo "  ✓ Ollama configuration saved\n";
echo "    Host: {$ollama_host}\n";
echo "    Port: {$ollama_port}\n";
echo "    Model: llama3.2\n";
echo "    Timeout: 30 seconds\n";

// Step 4: Test the integration
echo "\nStep 4: Testing AI integration...\n";

try {
  $ollama_service = \Drupal::service('billoria_core.ollama_ai');
  $status = $ollama_service->checkStatus();

  if ($status['available']) {
    echo "  ✓ AI service is available\n";

    // Test a simple query
    echo "\nStep 5: Testing AI response...\n";
    $test_response = $ollama_service->chat(
      "Say 'Hello from Billoria AI!' in one sentence.",
      ['type' => 'chatbot']
    );

    if ($test_response['success']) {
      echo "  ✓ AI responded successfully\n";
      echo "    Response: {$test_response['message']}\n";
    } else {
      echo "  ✗ AI response failed\n";
      echo "    Error: {$test_response['error']}\n";
    }
  } else {
    echo "  ✗ AI service is not available\n";
    echo "    Error: {$status['error']}\n";
  }
} catch (Exception $e) {
  echo "  ✗ Error testing AI integration\n";
  echo "    Error: " . $e->getMessage() . "\n";
}

// Step 6: Clear cache
echo "\nStep 6: Clearing Drupal cache...\n";
drupal_flush_all_caches();
echo "  ✓ Cache cleared\n";

// Summary
echo "\n╔════════════════════════════════════════╗\n";
echo "║         Configuration Complete!        ║\n";
echo "╚════════════════════════════════════════╝\n\n";

echo "✅ Ollama AI integration is ready!\n\n";
echo "API Endpoints available:\n";
echo "  POST /api/v1/ai/chat - AI chatbot\n";
echo "  POST /api/v1/ai/billboard-description - Generate descriptions\n";
echo "  POST /api/v1/ai/enhance-search - Enhanced search\n";
echo "  POST /api/v1/ai/recommendations - AI recommendations\n";
echo "  GET  /api/v1/ai/status - Service status (admin only)\n\n";

echo "Test the API:\n";
echo "  curl -X POST http://billoria-ad-api.ddev.site/api/v1/ai/chat \\\n";
echo "    -H 'Content-Type: application/json' \\\n";
echo "    -d '{\"message\": \"Hello, how can you help me?\"}'\n\n";

$logger->info('Ollama AI configuration completed successfully');
