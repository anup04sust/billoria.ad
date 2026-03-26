<?php

declare(strict_types=1);

namespace Drupal\billoria_core\EventSubscriber;

use Drupal\billoria_core\Service\NotificationService;
use Drupal\billoria_core\Service\ApiSecurityService;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Billoria Core Event Subscriber.
 *
 * Provides API security middleware for CSRF, rate limiting, and pattern detection.
 */
class BilloriaCoreSubscriber implements EventSubscriberInterface {

  /**
   * The notification service.
   *
   * @var \Drupal\billoria_core\Service\NotificationService
   */
  protected NotificationService $notificationService;

  /**
   * The API security service.
   *
   * @var \Drupal\billoria_core\Service\ApiSecurityService
   */
  protected $securityService;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a BilloriaCoreSubscriber object.
   *
   * @param \Drupal\billoria_core\Service\NotificationService $notification_service
   *   The notification service.
   * @param \Drupal\billoria_core\Service\ApiSecurityService $security_service
   *   The API security service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    NotificationService $notification_service,
    ApiSecurityService $security_service,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->notificationService = $notification_service;
    $this->securityService = $security_service;
    $this->logger = $logger_factory->get('billoria_core');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Run early to block requests before routing
    $events[KernelEvents::REQUEST][] = ['onRequest', 100];
    return $events;
  }

  /**
   * API security middleware - CSRF validation, rate limiting, pattern detection.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function onRequest(RequestEvent $event): void {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Only apply to /api/v1/* routes
    if (!str_starts_with($path, '/api/v1/')) {
      return;
    }

    // Skip CSRF token endpoint (no security check for getting token)
    if ($path === '/api/v1/csrf-token') {
      return;
    }

    // Map path to endpoint identifier
    $endpoint = $this->mapPathToEndpoint($path);

    // Perform comprehensive security check
    $securityCheck = $this->securityService->checkSecurity($request, $endpoint);

    // If not allowed, return error response immediately
    if (!$securityCheck['allowed']) {
      $statusCode = $securityCheck['status_code'] ?? 403;

      $response = new JsonResponse([
        'error' => $securityCheck['error'],
        'message' => $securityCheck['message'] ?? 'Security check failed',
      ], $statusCode);

      // Add rate limit headers if applicable
      if (isset($securityCheck['limit'])) {
        $response->headers->set('X-RateLimit-Limit', (string) $securityCheck['limit']);
        $response->headers->set('X-RateLimit-Remaining', '0');

        if (isset($securityCheck['retry_after'])) {
          $response->headers->set('Retry-After', (string) $securityCheck['retry_after']);
        }
      }

      $event->setResponse($response);
      $event->stopPropagation();
    }
  }

  /**
   * Map request path to endpoint identifier for rate limiting.
   *
   * @param string $path
   *   Request path.
   *
   * @return string
   *   Endpoint identifier (e.g., 'api_register').
   */
  protected function mapPathToEndpoint(string $path): string {
    $map = [
      '/api/v1/register' => 'api_register',
      '/api/v1/verify-email' => 'api_verify',
      '/api/v1/verify-phone' => 'api_verify',
      '/api/v1/resend-verification' => 'api_resend',
      '/api/v1/request-phone-otp' => 'api_resend',
    ];

    // Check for organization status endpoint pattern
    if (preg_match('#^/api/v1/organization/\d+/status$#', $path)) {
      return 'api_default';
    }

    return $map[$path] ?? 'api_default';
  }

}

