<?php

namespace Drupal\billoria_core\Service;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * API Security service - coordinates CSRF, rate limiting, and pattern detection.
 *
 * Uses Drupal core services (csrf_token, flood) with custom enhancements.
 */
class ApiSecurityService {

  /**
   * CSRF token generator (Drupal core).
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;

  /**
   * Flood service (Drupal core).
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * Pattern detection service.
   *
   * @var \Drupal\billoria_core\Service\PatternDetectionService
   */
  protected $patternDetector;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs an ApiSecurityService.
   */
  public function __construct(
    CsrfTokenGenerator $csrf_token,
    FloodInterface $flood,
    PatternDetectionService $pattern_detector,
    ConfigFactoryInterface $config_factory,
    RequestStack $request_stack,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->csrfToken = $csrf_token;
    $this->flood = $flood;
    $this->patternDetector = $pattern_detector;
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->logger = $logger_factory->get('billoria_core');
  }

  /**
   * Comprehensive security check for API endpoint.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $endpoint
   *   Endpoint identifier (e.g., 'api.register').
   *
   * @return array
   *   Security check result with 'allowed', 'error', 'status_code'.
   */
  public function checkSecurity(Request $request, string $endpoint): array {
    // 1. Check if security is globally disabled
    if ($this->isBypassEnabled()) {
      return ['allowed' => TRUE, 'bypass' => 'global_disabled'];
    }

    // 2. Check IP whitelist
    $ip = $request->getClientIp();
    if ($this->isWhitelistedIp($ip)) {
      return ['allowed' => TRUE, 'bypass' => 'whitelisted_ip'];
    }

    // 3. Check rate limit
    $rateCheck = $this->checkRateLimit($endpoint, $request);
    if (!$rateCheck['allowed']) {
      return $rateCheck; // Returns 429 response data
    }

    // 4. Pattern detection (for registration endpoint)
    if ($endpoint === 'api_register') {
      $patternCheck = $this->checkPatterns($request);
      if (!$patternCheck['allowed']) {
        return $patternCheck; // Returns 403 response data
      }
    }

    // 5. CSRF validation (for state-changing methods)
    if (in_array($request->getMethod(), ['POST', 'PATCH', 'PUT', 'DELETE'])) {
      $csrfCheck = $this->validateCsrf($request);
      if (!$csrfCheck['valid']) {
        return [
          'allowed' => FALSE,
          'error' => 'csrf_invalid',
          'message' => 'Invalid or missing CSRF token',
          'status_code' => 403,
        ];
      }
    }

    return ['allowed' => TRUE];
  }

  /**
   * Check if security is bypassed via settings.php.
   *
   * @return bool
   *   TRUE if security should be bypassed.
   */
  protected function isBypassEnabled(): bool {
    // Check settings.php: $settings['billoria_security_enabled']
    $enabled = Settings::get('billoria_security_enabled', TRUE);
    return !$enabled;
  }

  /**
   * Check if IP is whitelisted.
   *
   * @param string $ip
   *   IP address.
   *
   * @return bool
   *   TRUE if whitelisted.
   */
  protected function isWhitelistedIp(string $ip): bool {
    $config = $this->configFactory->get('billoria_core.security');
    $whitelistedIps = $config->get('rate_limit.bypass_ips') ?? [];
    return in_array($ip, $whitelistedIps);
  }

  /**
   * Check rate limit using Drupal flood service.
   *
   * @param string $endpoint
   *   Endpoint identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   Result with 'allowed', 'limit', 'remaining', 'retry_after'.
   */
  protected function checkRateLimit(string $endpoint, Request $request): array {
    $config = $this->configFactory->get('billoria_core.security');

    if (!$config->get('rate_limit.enabled')) {
      return ['allowed' => TRUE];
    }

    $endpointConfig = $config->get("rate_limit.endpoints.$endpoint");
    if (!$endpointConfig) {
      return ['allowed' => TRUE];
    }

    $limit = $endpointConfig['limit'];
    $window = $endpointConfig['window'];
    $identifierType = $endpointConfig['identifier'] ?? 'ip';

    // Build identifier based on type
    $identifier = $this->buildIdentifier($request, $identifierType);

    // Check flood
    $allowed = $this->flood->isAllowed($endpoint, $limit, $window, $identifier);

    if (!$allowed) {
      $this->logger->notice('Rate limit exceeded: @endpoint from @ip', [
        '@endpoint' => $endpoint,
        '@ip' => $request->getClientIp(),
      ]);

      return [
        'allowed' => FALSE,
        'error' => 'rate_limit_exceeded',
        'message' => 'Too many requests. Please try again later.',
        'status_code' => 429,
        'limit' => $limit,
        'window' => $window,
        'retry_after' => $window,
      ];
    }

    // Register this request
    $this->flood->register($endpoint, $window, $identifier);

    return [
      'allowed' => TRUE,
      'limit' => $limit,
      'remaining' => $limit - 1, // Approximate
    ];
  }

  /**
   * Build rate limit identifier based on type.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $type
   *   Identifier type: 'ip', 'ip+email', 'ip+user', 'user'.
   *
   * @return string
   *   The identifier string.
   */
  protected function buildIdentifier(Request $request, string $type): string {
    $ip = $request->getClientIp();

    switch ($type) {
      case 'ip':
        return $ip;

      case 'ip+email':
        $data = json_decode($request->getContent(), TRUE);
        $email = $data['user']['email'] ?? '';
        return $ip . ':' . $email;

      case 'ip+user':
        $userId = \Drupal::currentUser()->id();
        return $ip . ':user:' . $userId;

      case 'user':
        return 'user:' . \Drupal::currentUser()->id();

      default:
        return $ip;
    }
  }

  /**
   * Validate CSRF token using Drupal core service.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   Result with 'valid' flag.
   */
  protected function validateCsrf(Request $request): array {
    $config = $this->configFactory->get('billoria_core.security');

    if (!$config->get('csrf.enabled')) {
      return ['valid' => TRUE];
    }

    // Get token from header or JSON body
    $token = $request->headers->get('X-CSRF-Token');

    if (!$token) {
      $data = json_decode($request->getContent(), TRUE);
      $token = $data['csrf_token'] ?? NULL;
    }

    if (empty($token)) {
      return [
        'valid' => FALSE,
        'reason' => 'Missing CSRF token',
      ];
    }

    // Validate using Drupal core
    $isValid = $this->csrfToken->validate($token, 'rest');

    if (!$isValid) {
      $this->logger->notice('Invalid CSRF token from IP @ip', [
        '@ip' => $request->getClientIp(),
      ]);
    }

    return ['valid' => $isValid];
  }

  /**
   * Check patterns for registration requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   Result with 'allowed', 'error', 'message', 'status_code'.
   */
  protected function checkPatterns(Request $request): array {
    $data = json_decode($request->getContent(), TRUE);
    $email = $data['user']['email'] ?? '';
    $username = $data['user']['name'] ?? '';
    $ip = $request->getClientIp();

    // Check disposable email
    if ($this->patternDetector->isDisposableEmail($email)) {
      $this->patternDetector->logRegistrationAttempt(
        $ip,
        $email,
        $username,
        $request->headers->get('X-Client-Fingerprint'),
        'blocked',
        ['disposable_email' => TRUE]
      );

      return [
        'allowed' => FALSE,
        'error' => 'disposable_email',
        'message' => 'Disposable email addresses are not allowed',
        'status_code' => 403,
      ];
    }

    // Check sequential pattern
    $sequential = $this->patternDetector->detectSequentialEmail($email, $ip);
    if ($sequential['is_suspicious']) {
      $this->patternDetector->logRegistrationAttempt(
        $ip,
        $email,
        $username,
        $request->headers->get('X-Client-Fingerprint'),
        'blocked',
        ['sequential_pattern' => TRUE, 'matches' => $sequential['matches']]
      );

      return [
        'allowed' => FALSE,
        'error' => 'suspicious_pattern',
        'message' => 'Suspicious registration pattern detected',
        'status_code' => 429,
        'retry_after' => $sequential['block_duration'],
      ];
    }

    // Check frontend fingerprint
    $fingerprint = $this->patternDetector->validateFingerprint($request);
    if (!$fingerprint['valid']) {
      $this->patternDetector->logRegistrationAttempt(
        $ip,
        $email,
        $username,
        $request->headers->get('X-Client-Fingerprint'),
        'blocked',
        ['fingerprint_invalid' => TRUE]
      );

      return [
        'allowed' => FALSE,
        'error' => 'fingerprint_invalid',
        'message' => $fingerprint['reason'],
        'status_code' => 403,
      ];
    }

    return ['allowed' => TRUE];
  }

  /**
   * Generate CSRF token using Drupal core.
   *
   * @return array
   *   Token data with 'token' and 'expires_in'.
   */
  public function generateCsrfToken(): array {
    // Start session if needed (for anonymous users)
    $session = $this->requestStack->getCurrentRequest()->getSession();
    if (!$session->isStarted()) {
      $session->start();
    }

    $token = $this->csrfToken->get('rest');

    $config = $this->configFactory->get('billoria_core.security');
    $lifetime = $config->get('csrf.token_lifetime') ?? 1800;

    return [
      'token' => $token,
      'expires_in' => $lifetime,
    ];
  }

  /**
   * Log successful registration for pattern analysis.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $email
   *   Email address.
   * @param string $username
   *   Username.
   */
  public function logSuccessfulRegistration(Request $request, string $email, string $username): void {
    $this->patternDetector->logRegistrationAttempt(
      $request->getClientIp(),
      $email,
      $username,
      $request->headers->get('X-Client-Fingerprint'),
      'success',
      []
    );
  }

}
