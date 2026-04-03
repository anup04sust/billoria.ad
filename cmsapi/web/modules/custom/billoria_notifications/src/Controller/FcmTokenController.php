<?php

namespace Drupal\billoria_notifications\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\billoria_notifications\FirebaseService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST API controller for FCM token management.
 */
class FcmTokenController extends ControllerBase {

  /**
   * The Firebase service.
   *
   * @var \Drupal\billoria_notifications\FirebaseService
   */
  protected $firebaseService;

  /**
   * Constructs a FcmTokenController object.
   */
  public function __construct(FirebaseService $firebase_service) {
    $this->firebaseService = $firebase_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('billoria_notifications.firebase')
    );
  }

  /**
   * Registers a device token for push notifications.
   *
   * POST /api/v1/notifications/fcm/register
   *
   * Request body:
   * {
   *   "token": "fcm-device-token",
   *   "deviceType": "web",
   *   "deviceName": "Chrome on Windows"
   * }
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with registration status.
   */
  public function registerToken(Request $request): JsonResponse {
    if (!$this->currentUser()->isAuthenticated()) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'Authentication required',
      ], 401);
    }

    $data = json_decode($request->getContent(), TRUE);

    if (empty($data['token'])) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'FCM token is required',
      ], 400);
    }

    $token = $data['token'];
    $device_type = $data['deviceType'] ?? 'web';
    $device_name = $data['deviceName'] ?? NULL;

    $success = $this->firebaseService->registerToken(
      $this->currentUser()->id(),
      $token,
      $device_type,
      $device_name
    );

    if ($success) {
      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Device token registered successfully',
        'timestamp' => time(),
      ]);
    }

    return new JsonResponse([
      'success' => FALSE,
      'error' => 'Failed to register device token',
    ], 500);
  }

  /**
   * Unregisters a device token.
   *
   * POST /api/v1/notifications/fcm/unregister
   *
   * Request body:
   * {
   *   "token": "fcm-device-token"
   * }
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with unregistration status.
   */
  public function unregisterToken(Request $request): JsonResponse {
    if (!$this->currentUser()->isAuthenticated()) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'Authentication required',
      ], 401);
    }

    $data = json_decode($request->getContent(), TRUE);

    if (empty($data['token'])) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'FCM token is required',
      ], 400);
    }

    $success = $this->firebaseService->unregisterToken(
      $data['token'],
      $this->currentUser()->id()
    );

    if ($success) {
      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Device token unregistered successfully',
        'timestamp' => time(),
      ]);
    }

    return new JsonResponse([
      'success' => FALSE,
      'error' => 'Token not found or already unregistered',
    ], 404);
  }

  /**
   * Lists all registered tokens for the current user.
   *
   * GET /api/v1/notifications/fcm/tokens
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with list of tokens.
   */
  public function listTokens(): JsonResponse {
    if (!$this->currentUser()->isAuthenticated()) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'Authentication required',
      ], 401);
    }

    $tokens = $this->firebaseService->getUserTokens($this->currentUser()->id());

    // Remove sensitive token values from response.
    $sanitized_tokens = array_map(function ($token) {
      return [
        'id' => $token['id'],
        'deviceType' => $token['device_type'],
        'deviceName' => $token['device_name'],
        'isActive' => (bool) $token['is_active'],
        'created' => $token['created'],
        'updated' => $token['updated'],
        'tokenPreview' => substr($token['token'], 0, 20) . '...',
      ];
    }, $tokens);

    return new JsonResponse([
      'success' => TRUE,
      'data' => [
        'tokens' => $sanitized_tokens,
        'count' => count($sanitized_tokens),
      ],
      'timestamp' => time(),
    ]);
  }

}
