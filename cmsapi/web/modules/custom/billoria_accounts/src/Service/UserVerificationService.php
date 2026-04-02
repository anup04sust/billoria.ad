<?php

namespace Drupal\billoria_accounts\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for managing user verification records (email, phone, etc.).
 */
class UserVerificationService {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a UserVerificationService object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(Connection $database, AccountProxyInterface $current_user, LoggerInterface $logger) {
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->logger = $logger;
  }

  /**
   * Create a new verification record.
   *
   * @param int $uid
   *   User ID.
   * @param string $type
   *   Verification type (email, phone, sms, etc.).
   * @param string $identifier
   *   The value being verified (email address, phone number).
   * @param string $code
   *   The verification code (OTP, token).
   * @param int $expiry_seconds
   *   Seconds until expiry (default: 600 = 10 minutes).
   * @param int $max_attempts
   *   Maximum verification attempts (default: 5).
   * @param array $metadata
   *   Optional metadata (IP, user agent, etc.).
   *
   * @return int|false
   *   The verification ID or FALSE on failure.
   */
  public function createVerification($uid, $type, $identifier, $code, $expiry_seconds = 600, $max_attempts = 5, array $metadata = []) {
    try {
      $now = time();
      $code_hash = hash('sha256', $code);

      $record = [
        'uid' => $uid,
        'verification_type' => $type,
        'identifier' => $identifier,
        'code' => $code,
        'code_hash' => $code_hash,
        'status' => 'pending',
        'attempts' => 0,
        'max_attempts' => $max_attempts,
        'created' => $now,
        'expires' => $now + $expiry_seconds,
        'metadata' => !empty($metadata) ? json_encode($metadata) : NULL,
      ];

      return $this->database->insert('billoria_user_verifications')
        ->fields($record)
        ->execute();
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to create verification record: @message', ['@message' => $e->getMessage()]);
      return FALSE;
    }
  }

  /**
   * Get the latest pending verification for a user and type.
   *
   * @param int $uid
   *   User ID.
   * @param string $type
   *   Verification type.
   * @param string $identifier
   *   Optional identifier to match.
   *
   * @return object|null
   *   The verification record or NULL.
   */
  public function getLatestVerification($uid, $type, $identifier = NULL) {
    $query = $this->database->select('billoria_user_verifications', 'v')
      ->fields('v')
      ->condition('v.uid', $uid)
      ->condition('v.verification_type', $type)
      ->condition('v.status', 'pending')
      ->orderBy('v.created', 'DESC')
      ->range(0, 1);

    if ($identifier) {
      $query->condition('v.identifier', $identifier);
    }

    return $query->execute()->fetchObject();
  }

  /**
   * Verify a code and mark as verified if valid.
   *
   * @param int $verification_id
   *   Verification record ID.
   * @param string $code
   *   The code to verify.
   *
   * @return array
   *   Status array with 'success', 'message', 'data' keys.
   */
  public function verifyCode($verification_id, $code) {
    $record = $this->database->select('billoria_user_verifications', 'v')
      ->fields('v')
      ->condition('v.id', $verification_id)
      ->execute()
      ->fetchObject();

    if (!$record) {
      return [
        'success' => FALSE,
        'message' => 'Verification record not found.',
        'error' => 'not_found',
      ];
    }

    $now = time();

    // Check if expired.
    if ($record->expires < $now) {
      $this->updateVerificationStatus($verification_id, 'expired');
      return [
        'success' => FALSE,
        'message' => 'Verification code has expired.',
        'error' => 'expired',
      ];
    }

    // Check max attempts.
    if ($record->attempts >= $record->max_attempts) {
      $this->updateVerificationStatus($verification_id, 'failed');
      return [
        'success' => FALSE,
        'message' => 'Maximum verification attempts exceeded.',
        'error' => 'max_attempts',
      ];
    }

    // Increment attempts.
    $this->database->update('billoria_user_verifications')
      ->fields([
        'attempts' => $record->attempts + 1,
        'last_attempt_at' => $now,
      ])
      ->condition('id', $verification_id)
      ->execute();

    // Verify code using constant-time comparison.
    $code_hash = hash('sha256', $code);
    if (hash_equals($record->code_hash, $code_hash)) {
      $this->updateVerificationStatus($verification_id, 'verified', $now);
      return [
        'success' => TRUE,
        'message' => 'Verification successful.',
        'data' => [
          'verification_id' => $verification_id,
          'verification_type' => $record->verification_type,
          'identifier' => $record->identifier,
        ],
      ];
    }

    return [
      'success' => FALSE,
      'message' => 'Invalid verification code.',
      'error' => 'invalid_code',
      'data' => [
        'attempts_remaining' => $record->max_attempts - ($record->attempts + 1),
      ],
    ];
  }

  /**
   * Update verification status.
   *
   * @param int $verification_id
   *   Verification record ID.
   * @param string $status
   *   New status (verified, expired, failed, cancelled).
   * @param int|null $verified_at
   *   Optional verification timestamp.
   */
  public function updateVerificationStatus($verification_id, $status, $verified_at = NULL) {
    $fields = ['status' => $status];
    if ($verified_at) {
      $fields['verified_at'] = $verified_at;
    }

    $this->database->update('billoria_user_verifications')
      ->fields($fields)
      ->condition('id', $verification_id)
      ->execute();
  }

  /**
   * Cancel all pending verifications for a user and type.
   *
   * @param int $uid
   *   User ID.
   * @param string $type
   *   Verification type.
   */
  public function cancelPendingVerifications($uid, $type) {
    $this->database->update('billoria_user_verifications')
      ->fields(['status' => 'cancelled'])
      ->condition('uid', $uid)
      ->condition('verification_type', $type)
      ->condition('status', 'pending')
      ->execute();
  }

  /**
   * Check if user can request a new verification code (rate limiting).
   *
   * @param int $uid
   *   User ID.
   * @param string $type
   *   Verification type.
   * @param int $cooldown_seconds
   *   Cooldown period in seconds (default: 60).
   *
   * @return array
   *   Status array with 'allowed', 'message', 'retry_after' keys.
   */
  public function canRequestNewCode($uid, $type, $cooldown_seconds = 60) {
    $latest = $this->getLatestVerification($uid, $type);

    if ($latest) {
      $time_since_last = time() - $latest->created;
      if ($time_since_last < $cooldown_seconds) {
        $retry_after = $cooldown_seconds - $time_since_last;
        return [
          'allowed' => FALSE,
          'message' => 'Please wait before requesting a new code.',
          'retry_after' => $retry_after,
        ];
      }
    }

    return [
      'allowed' => TRUE,
      'message' => 'You can request a new code.',
    ];
  }

  /**
   * Clean up expired and old verification records.
   *
   * @param int $days_old
   *   Delete records older than this many days (default: 30).
   *
   * @return int
   *   Number of records deleted.
   */
  public function cleanupOldRecords($days_old = 30) {
    $cutoff = time() - ($days_old * 86400);

    return $this->database->delete('billoria_user_verifications')
      ->condition('created', $cutoff, '<')
      ->execute();
  }

  /**
   * Get verification statistics for a user.
   *
   * @param int $uid
   *   User ID.
   * @param string $type
   *   Optional verification type filter.
   *
   * @return array
   *   Statistics array.
   */
  public function getVerificationStats($uid, $type = NULL) {
    $query = $this->database->select('billoria_user_verifications', 'v')
      ->condition('v.uid', $uid);

    if ($type) {
      $query->condition('v.verification_type', $type);
    }

    $query->addExpression('COUNT(*)', 'total');
    $query->addExpression('SUM(CASE WHEN status = \'verified\' THEN 1 ELSE 0 END)', 'verified');
    $query->addExpression('SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END)', 'pending');
    $query->addExpression('SUM(CASE WHEN status = \'failed\' THEN 1 ELSE 0 END)', 'failed');
    $query->addExpression('SUM(CASE WHEN status = \'expired\' THEN 1 ELSE 0 END)', 'expired');

    $result = $query->execute()->fetchObject();

    return [
      'total' => (int) $result->total,
      'verified' => (int) $result->verified,
      'pending' => (int) $result->pending,
      'failed' => (int) $result->failed,
      'expired' => (int) $result->expired,
    ];
  }

  /**
   * Generate a random numeric OTP code.
   *
   * @param int $length
   *   Length of the code (default: 6).
   *
   * @return string
   *   The generated code.
   */
  public function generateOtpCode($length = 6) {
    $min = pow(10, $length - 1);
    $max = pow(10, $length) - 1;
    return (string) random_int($min, $max);
  }

}
