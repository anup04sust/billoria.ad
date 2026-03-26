<?php

namespace Drupal\billoria_core\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Pattern detection service for identifying suspicious registration patterns.
 *
 * Detects:
 * - Sequential email patterns (email1@, email2@, email3@)
 * - Disposable email domains
 * - Browser fingerprint abuse
 * - Velocity anomalies
 */
class PatternDetectionService {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a PatternDetectionService.
   */
  public function __construct(Connection $database, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->database = $database;
    $this->configFactory = $config_factory;
    $this->logger = $logger_factory->get('billoria_core');
  }

  /**
   * Detect sequential email pattern from same IP.
   *
   * Examples: email1@example.com, email2@example.com, email3@example.com
   *
   * @param string $email
   *   The email to check.
   * @param string $ip
   *   The IP address.
   *
   * @return array
   *   Array with 'is_suspicious', 'reason', 'action', 'block_duration'.
   */
  public function detectSequentialEmail(string $email, string $ip): array {
    $config = $this->configFactory->get('billoria_core.security');

    if (!$config->get('pattern_detection.sequential_emails.enabled')) {
      return ['is_suspicious' => FALSE];
    }

    $threshold = $config->get('pattern_detection.sequential_emails.threshold') ?? 3;
    $window = $config->get('pattern_detection.sequential_emails.window') ?? 3600;

    // Get recent registrations from this IP
    $recentEmails = $this->getRecentRegistrationEmails($ip, $window, 10);

    if (count($recentEmails) < ($threshold - 1)) {
      return ['is_suspicious' => FALSE];
    }

    // Extract pattern from current email
    $currentPattern = $this->extractEmailPattern($email);

    if (!$currentPattern) {
      return ['is_suspicious' => FALSE];
    }

    // Check if recent emails match sequential pattern
    $matches = 0;
    foreach ($recentEmails as $recentEmail) {
      $recentPattern = $this->extractEmailPattern($recentEmail);

      if ($recentPattern && $this->isSequentialMatch($currentPattern, $recentPattern)) {
        $matches++;
      }
    }

    if ($matches >= ($threshold - 1)) {
      $action = $config->get('pattern_detection.sequential_emails.action') ?? 'block';
      $blockDuration = $config->get('pattern_detection.sequential_emails.block_duration') ?? 86400;

      $this->logger->warning('Sequential email pattern detected: @email from IP @ip', [
        '@email' => $email,
        '@ip' => $ip,
      ]);

      return [
        'is_suspicious' => TRUE,
        'reason' => 'Sequential email pattern detected',
        'pattern' => $currentPattern['base'],
        'matches' => $matches,
        'action' => $action,
        'block_duration' => $blockDuration,
      ];
    }

    return ['is_suspicious' => FALSE];
  }

  /**
   * Check if email domain is disposable/temporary.
   *
   * @param string $email
   *   The email to check.
   *
   * @return bool
   *   TRUE if disposable domain.
   */
  public function isDisposableEmail(string $email): bool {
    $config = $this->configFactory->get('billoria_core.security');

    if (!$config->get('pattern_detection.disposable_domains.enabled')) {
      return FALSE;
    }

    $domain = strtolower(substr(strrchr($email, "@"), 1));
    $disposableDomains = $config->get('pattern_detection.disposable_domains.domains') ?? [];

    if (in_array($domain, $disposableDomains)) {
      $this->logger->notice('Disposable email detected: @email', ['@email' => $email]);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Validate frontend browser fingerprint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array
   *   Array with 'valid' and 'reason'.
   */
  public function validateFingerprint(Request $request): array {
    $config = $this->configFactory->get('billoria_core.security');

    if (!$config->get('pattern_detection.fingerprint.enabled')) {
      return ['valid' => TRUE];
    }

    $headerName = $config->get('pattern_detection.fingerprint.require_header') ?? 'X-Client-Fingerprint';
    $fingerprint = $request->headers->get($headerName);

    if (empty($fingerprint)) {
      return [
        'valid' => FALSE,
        'reason' => 'Missing browser fingerprint header',
      ];
    }

    // Validate fingerprint format (should be 32-64 character hash)
    if (!preg_match('/^[a-f0-9]{32,64}$/i', $fingerprint)) {
      return [
        'valid' => FALSE,
        'reason' => 'Invalid fingerprint format',
      ];
    }

    // Check fingerprint usage count
    $maxAccounts = $config->get('pattern_detection.fingerprint.max_accounts_per_fingerprint') ?? 5;
    $window = $config->get('pattern_detection.fingerprint.window') ?? 86400;

    $count = $this->getFingerprintUsageCount($fingerprint, $window);

    if ($count >= $maxAccounts) {
      $this->logger->warning('Fingerprint limit exceeded: @fingerprint (@count accounts)', [
        '@fingerprint' => substr($fingerprint, 0, 16) . '...',
        '@count' => $count,
      ]);

      return [
        'valid' => FALSE,
        'reason' => 'Too many accounts created from this browser',
        'count' => $count,
        'max' => $maxAccounts,
      ];
    }

    return ['valid' => TRUE];
  }

  /**
   * Log registration attempt for pattern analysis.
   *
   * @param string $ip
   *   IP address.
   * @param string $email
   *   Email address.
   * @param string $username
   *   Username.
   * @param string|null $fingerprint
   *   Browser fingerprint.
   * @param string $status
   *   Status: 'success', 'blocked', 'suspicious'.
   * @param array $patternFlags
   *   Pattern detection flags.
   */
  public function logRegistrationAttempt(string $ip, string $email, string $username, ?string $fingerprint, string $status, array $patternFlags = []): void {
    try {
      $this->database->insert('billoria_registration_log')
        ->fields([
          'ip' => $ip,
          'email' => $email,
          'username' => $username,
          'fingerprint' => $fingerprint,
          'timestamp' => \Drupal::time()->getRequestTime(),
          'status' => $status,
          'pattern_flags' => json_encode($patternFlags),
        ])
        ->execute();
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to log registration attempt: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Extract email pattern (base and number).
   *
   * Examples:
   * - email1@example.com → ['base' => 'email', 'number' => 1, 'domain' => 'example.com']
   * - test123@mail.com → ['base' => 'test', 'number' => 123, 'domain' => 'mail.com']
   *
   * @param string $email
   *   The email address.
   *
   * @return array|null
   *   Pattern data or NULL if no pattern.
   */
  protected function extractEmailPattern(string $email): ?array {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
      return NULL;
    }

    [$localPart, $domain] = $parts;

    // Match pattern: letters followed by digits at the end
    // Examples: email1, test123, user001
    if (preg_match('/^([a-z]+?)(\d+)$/i', $localPart, $matches)) {
      return [
        'base' => strtolower($matches[1]),
        'number' => (int) $matches[2],
        'domain' => strtolower($domain),
        'full_pattern' => strtolower($matches[1]) . '@' . strtolower($domain),
      ];
    }

    return NULL;
  }

  /**
   * Check if two email patterns are sequential.
   *
   * @param array $pattern1
   *   First pattern.
   * @param array $pattern2
   *   Second pattern.
   *
   * @return bool
   *   TRUE if patterns match and numbers are sequential.
   */
  protected function isSequentialMatch(array $pattern1, array $pattern2): bool {
    // Must have same base and domain
    if ($pattern1['base'] !== $pattern2['base'] ||
        $pattern1['domain'] !== $pattern2['domain']) {
      return FALSE;
    }

    // Numbers should be close (within 10)
    $diff = abs($pattern1['number'] - $pattern2['number']);
    return $diff > 0 && $diff <= 10;
  }

  /**
   * Get recent registration emails from IP.
   *
   * @param string $ip
   *   IP address.
   * @param int $window
   *   Time window in seconds.
   * @param int $limit
   *   Max number of results.
   *
   * @return array
   *   Array of email addresses.
   */
  protected function getRecentRegistrationEmails(string $ip, int $window, int $limit): array {
    $timestamp = \Drupal::time()->getRequestTime() - $window;

    try {
      $result = $this->database->select('billoria_registration_log', 'r')
        ->fields('r', ['email'])
        ->condition('ip', $ip)
        ->condition('timestamp', $timestamp, '>=')
        ->orderBy('timestamp', 'DESC')
        ->range(0, $limit)
        ->execute()
        ->fetchCol();

      return $result ?: [];
    }
    catch (\Exception $e) {
      // Table might not exist yet
      return [];
    }
  }

  /**
   * Get fingerprint usage count.
   *
   * @param string $fingerprint
   *   Browser fingerprint.
   * @param int $window
   *   Time window in seconds.
   *
   * @return int
   *   Number of accounts created with this fingerprint.
   */
  protected function getFingerprintUsageCount(string $fingerprint, int $window): int {
    $timestamp = \Drupal::time()->getRequestTime() - $window;

    try {
      $count = $this->database->select('billoria_registration_log', 'r')
        ->condition('fingerprint', $fingerprint)
        ->condition('timestamp', $timestamp, '>=')
        ->condition('status', 'success')
        ->countQuery()
        ->execute()
        ->fetchField();

      return (int) $count;
    }
    catch (\Exception $e) {
      return 0;
    }
  }

}
