<?php

namespace Drupal\billoria_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\billoria_core\Service\ApiSecurityService;

/**
 * API Security controller for CSRF tokens.
 */
class ApiSecurityController extends ControllerBase {

  /**
   * API security service.
   *
   * @var \Drupal\billoria_core\Service\ApiSecurityService
   */
  protected $securityService;

  /**
   * Constructs a new ApiSecurityController.
   */
  public function __construct(ApiSecurityService $security_service) {
    $this->securityService = $security_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('billoria_core.security')
    );
  }

  /**
   * Get CSRF token for API requests.
   *
   * GET /api/v1/csrf-token
   *
   * Returns a CSRF token that must be included in POST/PATCH/DELETE requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with CSRF token.
   *
   * @code
   * Response format:
   * {
   *   "csrf_token": "abc123...",
   *   "expires_in": 1800,
   *   "usage": {
   *     "header": "X-CSRF-Token: abc123...",
   *     "or": "Include 'csrf_token' in JSON body"
   *   }
   * }
   * @endcode
   */
  public function getCsrfToken(Request $request): JsonResponse {
    $tokenData = $this->securityService->generateCsrfToken();

    return new JsonResponse([
      'csrf_token' => $tokenData['token'],
      'expires_in' => $tokenData['expires_in'],
      'usage' => [
        'header' => 'X-CSRF-Token: ' . $tokenData['token'],
        'or' => "Include 'csrf_token' in JSON request body",
      ],
    ]);
  }

}
