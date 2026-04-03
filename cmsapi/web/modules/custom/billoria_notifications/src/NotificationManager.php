<?php

namespace Drupal\billoria_notifications;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Service for managing user notifications.
 */
class NotificationManager {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The Firebase service.
   *
   * @var \Drupal\billoria_notifications\FirebaseService
   */
  protected $firebaseService;

  /**
   * Constructs a NotificationManager object.
   */
  public function __construct(
    Connection $database,
    AccountProxyInterface $current_user,
    TimeInterface $time,
    LoggerChannelFactoryInterface $logger_factory,
    FirebaseService $firebase_service
  ) {
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->time = $time;
    $this->logger = $logger_factory->get('billoria_notifications');
    $this->firebaseService = $firebase_service;
  }

  /**
   * Creates a new notification.
   *
   * @param int $uid
   *   The user ID to notify.
   * @param string $type
   *   The notification type (booking, verification, system, etc.).
   * @param string $title
   *   The notification title.
   * @param string $message
   *   The notification message.
   * @param array $metadata
   *   Optional metadata (entity IDs, action URLs, etc.).
   * @param string $priority
   *   Priority level: low, normal, high, urgent.
   * @param int|null $expires_at
   *   Optional expiration timestamp.
   * @param bool $send_push
   *   Whether to send push notification (default: TRUE).
   *
   * @return int|false
   *   The notification ID or FALSE on failure.
   */
  public function createNotification(
    int $uid,
    string $type,
    string $title,
    string $message,
    array $metadata = [],
    string $priority = 'normal',
    ?int $expires_at = NULL,
    bool $send_push = TRUE
  ) {
    try {
      $nid = $this->database->insert('billoria_notifications')
        ->fields([
          'uid' => $uid,
          'type' => $type,
          'title' => $title,
          'message' => $message,
          'metadata' => !empty($metadata) ? json_encode($metadata) : NULL,
          'is_read' => 0,
          'priority' => $priority,
          'created' => $this->time->getRequestTime(),
          'expires_at' => $expires_at,
        ])
        ->execute();

      $this->logger->info('Notification created for user @uid: @title', [
        '@uid' => $uid,
        '@title' => $title,
      ]);

      // Send push notification if enabled.
      if ($send_push) {
        $this->firebaseService->sendPushNotification($uid, $title, $message, [
          'notification_id' => $nid,
          'type' => $type,
          'priority' => $priority,
        ] + $metadata);
      }

      return $nid;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to create notification: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Gets notifications for a user.
   *
   * @param int|null $uid
   *   The user ID. NULL for current user.
   * @param int $limit
   *   Maximum number of notifications to return.
   * @param int $offset
   *   Offset for pagination.
   * @param bool|null $is_read
   *   Filter by read status. NULL for all notifications.
   * @param string|null $type
   *   Filter by notification type.
   *
   * @return array
   *   Array of notification objects.
   */
  public function getNotifications(
    ?int $uid = NULL,
    int $limit = 50,
    int $offset = 0,
    ?bool $is_read = NULL,
    ?string $type = NULL
  ): array {
    $uid = $uid ?? $this->currentUser->id();

    $query = $this->database->select('billoria_notifications', 'n')
      ->fields('n')
      ->condition('n.uid', $uid)
      ->orderBy('n.created', 'DESC')
      ->range($offset, $limit);

    // Filter expired notifications.
    $current_time = $this->time->getRequestTime();
    $query->condition(
      $query->orConditionGroup()
        ->isNull('n.expires_at')
        ->condition('n.expires_at', $current_time, '>')
    );

    if ($is_read !== NULL) {
      $query->condition('n.is_read', $is_read ? 1 : 0);
    }

    if ($type !== NULL) {
      $query->condition('n.type', $type);
    }

    $results = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    // Decode metadata JSON.
    foreach ($results as &$notification) {
      $notification['metadata'] = !empty($notification['metadata']) 
        ? json_decode($notification['metadata'], TRUE) 
        : [];
      $notification['is_read'] = (bool) $notification['is_read'];
    }

    return $results;
  }

  /**
   * Gets the count of unread notifications for a user.
   *
   * @param int|null $uid
   *   The user ID. NULL for current user.
   *
   * @return int
   *   The count of unread notifications.
   */
  public function getUnreadCount(?int $uid = NULL): int {
    $uid = $uid ?? $this->currentUser->id();
    $current_time = $this->time->getRequestTime();

    $query = $this->database->select('billoria_notifications', 'n')
      ->condition('n.uid', $uid)
      ->condition('n.is_read', 0);

    // Exclude expired notifications.
    $query->condition(
      $query->orConditionGroup()
        ->isNull('n.expires_at')
        ->condition('n.expires_at', $current_time, '>')
    );

    return (int) $query->countQuery()->execute()->fetchField();
  }

  /**
   * Marks a notification as read.
   *
   * @param int $nid
   *   The notification ID.
   * @param int|null $uid
   *   The user ID for verification. NULL for current user.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function markAsRead(int $nid, ?int $uid = NULL): bool {
    $uid = $uid ?? $this->currentUser->id();

    try {
      $updated = $this->database->update('billoria_notifications')
        ->fields([
          'is_read' => 1,
          'read_at' => $this->time->getRequestTime(),
        ])
        ->condition('nid', $nid)
        ->condition('uid', $uid)
        ->execute();

      return $updated > 0;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to mark notification as read: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Marks all notifications as read for a user.
   *
   * @param int|null $uid
   *   The user ID. NULL for current user.
   *
   * @return int
   *   The number of notifications marked as read.
   */
  public function markAllAsRead(?int $uid = NULL): int {
    $uid = $uid ?? $this->currentUser->id();

    try {
      $updated = $this->database->update('billoria_notifications')
        ->fields([
          'is_read' => 1,
          'read_at' => $this->time->getRequestTime(),
        ])
        ->condition('uid', $uid)
        ->condition('is_read', 0)
        ->execute();

      return $updated;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to mark all notifications as read: @error', [
        '@error' => $e->getMessage(),
      ]);
      return 0;
    }
  }

  /**
   * Deletes a notification.
   *
   * @param int $nid
   *   The notification ID.
   * @param int|null $uid
   *   The user ID for verification. NULL for current user.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function deleteNotification(int $nid, ?int $uid = NULL): bool {
    $uid = $uid ?? $this->currentUser->id();

    try {
      $deleted = $this->database->delete('billoria_notifications')
        ->condition('nid', $nid)
        ->condition('uid', $uid)
        ->execute();

      return $deleted > 0;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to delete notification: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Deletes expired notifications (cleanup task).
   *
   * @return int
   *   The number of notifications deleted.
   */
  public function deleteExpiredNotifications(): int {
    $current_time = $this->time->getRequestTime();

    try {
      $deleted = $this->database->delete('billoria_notifications')
        ->condition('expires_at', $current_time, '<')
        ->isNotNull('expires_at')
        ->execute();

      if ($deleted > 0) {
        $this->logger->info('Deleted @count expired notifications.', [
          '@count' => $deleted,
        ]);
      }

      return $deleted;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to delete expired notifications: @error', [
        '@error' => $e->getMessage(),
      ]);
      return 0;
    }
  }

}
