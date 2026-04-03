<?php

namespace Drupal\billoria_notifications\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\billoria_notifications\NotificationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST API controller for user notifications.
 */
class NotificationApiController extends ControllerBase {

  /**
   * The notification manager service.
   *
   * @var \Drupal\billoria_notifications\NotificationManager
   */
  protected $notificationManager;

  /**
   * Constructs a NotificationApiController object.
   */
  public function __construct(NotificationManager $notification_manager) {
    $this->notificationManager = $notification_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('billoria_notifications.manager')
    );
  }

  /**
   * Lists notifications for the current user.
   *
   * GET /api/v1/notifications
   *
   * Query parameters:
   * - limit: Max notifications to return (default: 50, max: 100)
   * - offset: Offset for pagination (default: 0)
   * - unread_only: Filter to unread notifications (default: false)
   * - type: Filter by notification type
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with notifications.
   */
  public function list(Request $request): JsonResponse {
    if (!$this->currentUser()->isAuthenticated()) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'Authentication required',
      ], 401);
    }

    $limit = min((int) $request->query->get('limit', 50), 100);
    $offset = (int) $request->query->get('offset', 0);
    $unread_only = $request->query->get('unread_only') === 'true';
    $type = $request->query->get('type');

    $notifications = $this->notificationManager->getNotifications(
      NULL,
      $limit,
      $offset,
      $unread_only ? FALSE : NULL,
      $type
    );

    $unread_count = $this->notificationManager->getUnreadCount();

    return new JsonResponse([
      'success' => TRUE,
      'data' => [
        'notifications' => $notifications,
        'unreadCount' => $unread_count,
        'pagination' => [
          'limit' => $limit,
          'offset' => $offset,
          'hasMore' => count($notifications) === $limit,
        ],
      ],
      'timestamp' => time(),
    ]);
  }

  /**
   * Gets the count of unread notifications.
   *
   * GET /api/v1/notifications/unread-count
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with unread count.
   */
  public function unreadCount(): JsonResponse {
    if (!$this->currentUser()->isAuthenticated()) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'Authentication required',
      ], 401);
    }

    $count = $this->notificationManager->getUnreadCount();

    return new JsonResponse([
      'success' => TRUE,
      'data' => [
        'unreadCount' => $count,
      ],
      'timestamp' => time(),
    ]);
  }

  /**
   * Marks a notification as read.
   *
   * POST /api/v1/notifications/{nid}/mark-read
   *
   * @param int $nid
   *   The notification ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with success status.
   */
  public function markAsRead(int $nid): JsonResponse {
    if (!$this->currentUser()->isAuthenticated()) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'Authentication required',
      ], 401);
    }

    $success = $this->notificationManager->markAsRead($nid);

    if ($success) {
      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Notification marked as read',
        'data' => [
          'unreadCount' => $this->notificationManager->getUnreadCount(),
        ],
        'timestamp' => time(),
      ]);
    }

    return new JsonResponse([
      'success' => FALSE,
      'error' => 'Notification not found or access denied',
    ], 404);
  }

  /**
   * Marks all notifications as read for the current user.
   *
   * POST /api/v1/notifications/mark-all-read
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with success status.
   */
  public function markAllAsRead(): JsonResponse {
    if (!$this->currentUser()->isAuthenticated()) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'Authentication required',
      ], 401);
    }

    $updated = $this->notificationManager->markAllAsRead();

    return new JsonResponse([
      'success' => TRUE,
      'message' => sprintf('Marked %d notifications as read', $updated),
      'data' => [
        'updatedCount' => $updated,
        'unreadCount' => 0,
      ],
      'timestamp' => time(),
    ]);
  }

  /**
   * Deletes a notification.
   *
   * DELETE /api/v1/notifications/{nid}
   *
   * @param int $nid
   *   The notification ID.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response with success status.
   */
  public function delete(int $nid): JsonResponse {
    if (!$this->currentUser()->isAuthenticated()) {
      return new JsonResponse([
        'success' => FALSE,
        'error' => 'Authentication required',
      ], 401);
    }

    $success = $this->notificationManager->deleteNotification($nid);

    if ($success) {
      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Notification deleted',
        'data' => [
          'unreadCount' => $this->notificationManager->getUnreadCount(),
        ],
        'timestamp' => time(),
      ]);
    }

    return new JsonResponse([
      'success' => FALSE,
      'error' => 'Notification not found or access denied',
    ], 404);
  }

}
