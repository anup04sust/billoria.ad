<?php

namespace Drupal\billoria_notifications;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

/**
 * Service for managing Firebase Cloud Messaging (FCM) push notifications.
 */
class FirebaseService {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Firebase messaging instance.
   *
   * @var \Kreait\Firebase\Contract\Messaging|null
   */
  protected $messaging;

  /**
   * Constructs a FirebaseService object.
   */
  public function __construct(
    Connection $database,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory
  ) {
    $this->database = $database;
    $this->logger = $logger_factory->get('billoria_notifications');
    $this->configFactory = $config_factory;
    $this->initializeFirebase();
  }

  /**
   * Initializes Firebase Messaging client.
   */
  protected function initializeFirebase() {
    $config = $this->configFactory->get('billoria_notifications.firebase');
    $use_v1 = $config->get('use_v1_api') ?? TRUE;

    if ($use_v1) {
      $service_account_json = $config->get('service_account_json');
      
      if (!empty($service_account_json)) {
        try {
          $serviceAccount = json_decode($service_account_json, TRUE);
          if ($serviceAccount) {
            $factory = (new Factory)->withServiceAccount($serviceAccount);
            $this->messaging = $factory->createMessaging();
            $this->logger->info('Firebase V1 API initialized successfully');
          }
        }
        catch (\Exception $e) {
          $this->logger->error('Failed to initialize Firebase V1 API: @error', [
            '@error' => $e->getMessage(),
          ]);
        }
      }
    }
  }

  /**
   * Registers a device token for push notifications.
   *
   * @param int $uid
   *   The user ID.
   * @param string $token
   *   The FCM device token.
   * @param string $device_type
   *   The device type (web, android, ios).
   * @param string|null $device_name
   *   Optional device name/identifier.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function registerToken(int $uid, string $token, string $device_type = 'web', ?string $device_name = NULL): bool {
    try {
      $time = time();

      // Check if token already exists.
      $existing = $this->database->select('billoria_fcm_tokens', 'f')
        ->fields('f', ['id', 'uid'])
        ->condition('token', $token)
        ->execute()
        ->fetchAssoc();

      if ($existing) {
        // Update existing token.
        $this->database->update('billoria_fcm_tokens')
          ->fields([
            'uid' => $uid,
            'device_type' => $device_type,
            'device_name' => $device_name,
            'is_active' => 1,
            'updated' => $time,
          ])
          ->condition('token', $token)
          ->execute();

        $this->logger->info('Updated FCM token for user @uid', ['@uid' => $uid]);
      }
      else {
        // Insert new token.
        $this->database->insert('billoria_fcm_tokens')
          ->fields([
            'uid' => $uid,
            'token' => $token,
            'device_type' => $device_type,
            'device_name' => $device_name,
            'is_active' => 1,
            'created' => $time,
            'updated' => $time,
          ])
          ->execute();

        $this->logger->info('Registered new FCM token for user @uid', ['@uid' => $uid]);
      }

      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to register FCM token: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Unregisters a device token.
   *
   * @param string $token
   *   The FCM device token to unregister.
   * @param int|null $uid
   *   Optional user ID for verification.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function unregisterToken(string $token, ?int $uid = NULL): bool {
    try {
      $query = $this->database->delete('billoria_fcm_tokens')
        ->condition('token', $token);

      if ($uid !== NULL) {
        $query->condition('uid', $uid);
      }

      $deleted = $query->execute();

      if ($deleted > 0) {
        $this->logger->info('Unregistered FCM token');
        return TRUE;
      }

      return FALSE;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to unregister FCM token: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Gets all active tokens for a user.
   *
   * @param int $uid
   *   The user ID.
   *
   * @return array
   *   Array of token objects.
   */
  public function getUserTokens(int $uid): array {
    return $this->database->select('billoria_fcm_tokens', 'f')
      ->fields('f')
      ->condition('f.uid', $uid)
      ->condition('f.is_active', 1)
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Sends a push notification to a user's devices.
   *
   * @param int $uid
   *   The user ID to notify.
   * @param string $title
   *   The notification title.
   * @param string $body
   *   The notification body.
   * @param array $data
   *   Optional additional data payload.
   *
   * @return array
   *   Array with 'success' count and 'failed' count.
   */
  public function sendPushNotification(int $uid, string $title, string $body, array $data = []): array {
    $tokens = $this->getUserTokens($uid);

    if (empty($tokens)) {
      $this->logger->debug('No FCM tokens found for user @uid', ['@uid' => $uid]);
      return ['success' => 0, 'failed' => 0];
    }

    $success_count = 0;
    $failed_count = 0;

    foreach ($tokens as $token_data) {
      $result = $this->sendToToken($token_data['token'], $title, $body, $data);

      if ($result) {
        $success_count++;
      }
      else {
        $failed_count++;
        // Mark token as inactive if it failed.
        $this->markTokenInactive($token_data['token']);
      }
    }

    $this->logger->info('Sent push notification to user @uid: @success success, @failed failed', [
      '@uid' => $uid,
      '@success' => $success_count,
      '@failed' => $failed_count,
    ]);

    return ['success' => $success_count, 'failed' => $failed_count];
  }

  /**
   * Sends a notification to a specific FCM token.
   *
   * @param string $token
   *   The FCM device token.
   * @param string $title
   *   The notification title.
   * @param string $body
   *   The notification body.
   * @param array $data
   *   Optional additional data payload.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  protected function sendToToken(string $token, string $title, string $body, array $data = []): bool {
    $config = $this->configFactory->get('billoria_notifications.firebase');
    $use_v1 = $config->get('use_v1_api') ?? TRUE;

    if ($use_v1) {
      return $this->sendViaV1Api($token, $title, $body, $data);
    }
    else {
      return $this->sendViaLegacyApi($token, $title, $body, $data);
    }
  }

  /**
   * Sends notification via Firebase V1 API (recommended).
   */
  protected function sendViaV1Api(string $token, string $title, string $body, array $data = []): bool {
    if (!$this->messaging) {
      $this->logger->warning('Firebase V1 API not initialized. Please configure service account JSON.');
      return FALSE;
    }

    try {
      $message = CloudMessage::fromArray([
        'token' => $token,
        'notification' => [
          'title' => $title,
          'body' => $body,
        ],
        'data' => array_merge([
          'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
          'timestamp' => (string) time(),
        ], $data),
        'webpush' => [
          'fcm_options' => [
            'link' => $data['url'] ?? '/',
          ],
        ],
      ]);

      $this->messaging->send($message);
      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error('FCM V1 send failed: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Sends notification via Legacy FCM API (deprecated).
   */
  protected function sendViaLegacyApi(string $token, string $title, string $body, array $data = []): bool {
    $config = $this->configFactory->get('billoria_notifications.firebase');
    $server_key = $config->get('server_key');

    if (empty($server_key)) {
      $this->logger->warning('Firebase server key not configured.');
      return FALSE;
    }

    $fcm_url = 'https://fcm.googleapis.com/fcm/send';

    $notification = [
      'title' => $title,
      'body' => $body,
      'icon' => '/icon-192x192.png',
      'badge' => '/icon-192x192.png',
      'sound' => 'default',
    ];

    $payload = [
      'to' => $token,
      'notification' => $notification,
      'data' => array_merge([
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        'timestamp' => time(),
      ], $data),
      'priority' => 'high',
    ];

    $headers = [
      'Authorization: key=' . $server_key,
      'Content-Type: application/json',
    ];

    try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $fcm_url);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

      $response = curl_exec($ch);
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($http_code === 200) {
        $result = json_decode($response, TRUE);
        if (isset($result['success']) && $result['success'] > 0) {
          return TRUE;
        }
      }

      $this->logger->warning('FCM send failed (HTTP @code): @response', [
        '@code' => $http_code,
        '@response' => $response,
      ]);

      return FALSE;
    }
    catch (\Exception $e) {
      $this->logger->error('FCM send exception: @error', [
        '@error' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Marks a token as inactive.
   *
   * @param string $token
   *   The FCM device token.
   */
  protected function markTokenInactive(string $token): void {
    try {
      $this->database->update('billoria_fcm_tokens')
        ->fields(['is_active' => 0])
        ->condition('token', $token)
        ->execute();
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to mark token inactive: @error', [
        '@error' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Cleans up inactive tokens older than configured retention days.
   *
   * @return int
   *   The number of tokens deleted.
   */
  public function cleanupInactiveTokens(): int {
    $config = $this->configFactory->get('billoria_notifications.firebase');
    
    // Check if cleanup is enabled.
    if (!$config->get('cleanup_enabled')) {
      return 0;
    }

    $retention_days = $config->get('token_retention_days') ?? 30;
    $threshold = strtotime("-{$retention_days} days");

    try {
      $deleted = $this->database->delete('billoria_fcm_tokens')
        ->condition('is_active', 0)
        ->condition('updated', $threshold, '<')
        ->execute();

      if ($deleted > 0) {
        $this->logger->info('Cleaned up @count inactive FCM tokens (older than @days days)', [
          '@count' => $deleted,
          '@days' => $retention_days,
        ]);
      }

      return $deleted;
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to cleanup inactive tokens: @error', [
        '@error' => $e->getMessage(),
      ]);
      return 0;
    }
  }

}
