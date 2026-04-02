# User Verification System

## Overview

A generic, reusable database table and service for handling user verification processes (email, phone, SMS, etc.) in the Billoria platform.

## Database Schema

**Table**: `billoria_user_verifications`

### Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | serial | Primary key |
| `uid` | int | User ID (references users table) |
| `verification_type` | varchar(32) | Type: email, phone, sms, etc. |
| `identifier` | varchar(255) | Value being verified (email@example.com, +8801234567890) |
| `code` | varchar(32) | Verification code (OTP, token) |
| `code_hash` | varchar(64) | SHA-256 hash of code for secure comparison |
| `status` | varchar(16) | pending, verified, expired, failed, cancelled |
| `attempts` | tinyint | Number of verification attempts made |
| `max_attempts` | tinyint | Maximum attempts allowed (default: 5) |
| `created` | int | Unix timestamp when created |
| `expires` | int | Unix timestamp when expires |
| `verified_at` | int | Unix timestamp when verified (NULL if not verified) |
| `last_attempt_at` | int | Unix timestamp of last attempt |
| `metadata` | text | JSON-encoded metadata (IP, user agent, delivery status) |

### Indexes

- Primary key: `id`
- Index on `uid`, `verification_type`, `identifier`, `status`, `created`, `expires`
- Composite indexes: `uid_type`, `uid_type_status`

## Installation

```bash
cd cmsapi
ddev drush scr scripts/install-verification-table.php
```

## Service Usage

### Inject the Service

```php
$verificationService = \Drupal::service('billoria_accounts.user_verification');
```

### Example 1: Email Verification Flow

```php
// 1. Generate and send OTP
$uid = 123;
$email = 'user@example.com';
$otp = $verificationService->generateOtpCode(6); // 6-digit code

// Cancel any pending verifications
$verificationService->cancelPendingVerifications($uid, 'email');

// Create new verification record
$verification_id = $verificationService->createVerification(
  $uid,
  'email',
  $email,
  $otp,
  600, // 10 minutes expiry
  5,   // 5 max attempts
  ['ip' => \Drupal::request()->getClientIp()]
);

// Send email with $otp to user...

// 2. Verify the code
$result = $verificationService->verifyCode($verification_id, $user_input_code);

if ($result['success']) {
  // Mark email as verified in user entity
  $user = \Drupal\user\Entity\User::load($uid);
  $user->set('field_email_verified', TRUE);
  $user->save();
}
```

### Example 2: Phone/SMS Verification

```php
$uid = 456;
$phone = '+8801712345678';
$otp = $verificationService->generateOtpCode(6);

// Check rate limiting (60-second cooldown)
$rate_check = $verificationService->canRequestNewCode($uid, 'phone', 60);
if (!$rate_check['allowed']) {
  return [
    'error' => 'rate_limit',
    'message' => $rate_check['message'],
    'retry_after' => $rate_check['retry_after'],
  ];
}

// Cancel pending and create new
$verificationService->cancelPendingVerifications($uid, 'phone');
$verification_id = $verificationService->createVerification(
  $uid,
  'phone',
  $phone,
  $otp,
  300, // 5 minutes for SMS
  3    // 3 max attempts for SMS
);

// Send SMS via provider...

// Later: verify
$result = $verificationService->verifyCode($verification_id, $user_input);
```

### Example 3: Get Latest Verification

```php
$latest = $verificationService->getLatestVerification($uid, 'email');

if ($latest) {
  echo "Status: {$latest->status}\n";
  echo "Attempts: {$latest->attempts}/{$latest->max_attempts}\n";
  echo "Expires: " . date('Y-m-d H:i:s', $latest->expires) . "\n";
}
```

### Example 4: Check Rate Limiting

```php
$rate_check = $verificationService->canRequestNewCode($uid, 'email', 60);

if (!$rate_check['allowed']) {
  $response = [
    'error' => 'Too many requests. Try again in ' . $rate_check['retry_after'] . ' seconds.',
  ];
}
```

### Example 5: Cleanup Old Records (Cron)

```php
// In a cron job, clean up records older than 30 days
$deleted = $verificationService->cleanupOldRecords(30);
\Drupal::logger('billoria_accounts')->info('Cleaned up @count old verification records.', ['@count' => $deleted]);
```

### Example 6: Get Statistics

```php
$stats = $verificationService->getVerificationStats($uid, 'email');

// Returns:
// [
//   'total' => 10,
//   'verified' => 8,
//   'pending' => 1,
//   'failed' => 1,
//   'expired' => 0,
// ]
```

## REST API Controller Example

```php
<?php

namespace Drupal\billoria_accounts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\billoria_accounts\Service\UserVerificationService;

class VerificationApiController extends ControllerBase {

  protected $verificationService;

  public function __construct(UserVerificationService $verification_service) {
    $this->verificationService = $verification_service;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('billoria_accounts.user_verification')
    );
  }

  /**
   * Send email verification code.
   *
   * POST /api/v1/verification/email/send
   */
  public function sendEmailCode(Request $request) {
    $uid = $this->currentUser()->id();
    $user = \Drupal\user\Entity\User::load($uid);

    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    $email = $user->getEmail();

    // Rate limiting
    $rate_check = $this->verificationService->canRequestNewCode($uid, 'email', 60);
    if (!$rate_check['allowed']) {
      return new JsonResponse([
        'error' => 'rate_limit',
        'message' => $rate_check['message'],
        'retryAfter' => $rate_check['retry_after'],
      ], 429);
    }

    // Generate OTP
    $otp = $this->verificationService->generateOtpCode(6);

    // Cancel pending and create new
    $this->verificationService->cancelPendingVerifications($uid, 'email');
    $verification_id = $this->verificationService->createVerification(
      $uid,
      'email',
      $email,
      $otp,
      600, // 10 minutes
      5
    );

    // TODO: Send email with $otp
    // mail($email, 'Verification Code', "Your code: $otp");

    return new JsonResponse([
      'success' => true,
      'message' => 'Verification code sent to your email.',
      'verificationId' => $verification_id,
    ]);
  }

  /**
   * Verify email code.
   *
   * POST /api/v1/verification/email/verify
   * Body: { "code": "123456" }
   */
  public function verifyEmailCode(Request $request) {
    $uid = $this->currentUser()->id();
    $data = json_decode($request->getContent(), TRUE);
    $code = $data['code'] ?? '';

    if (empty($code)) {
      return new JsonResponse(['error' => 'Code is required'], 400);
    }

    // Get latest pending verification
    $latest = $this->verificationService->getLatestVerification($uid, 'email');

    if (!$latest) {
      return new JsonResponse(['error' => 'No pending verification found'], 404);
    }

    // Verify
    $result = $this->verificationService->verifyCode($latest->id, $code);

    if ($result['success']) {
      // Mark user email as verified
      $user = \Drupal\user\Entity\User::load($uid);
      $user->set('field_email_verified', TRUE);
      $user->save();
    }

    return new JsonResponse($result, $result['success'] ? 200 : 400);
  }

}
```

## Security Features

- **Hash-based verification**: Codes stored as SHA-256 hashes
- **Constant-time comparison**: Prevents timing attacks using `hash_equals()`
- **Rate limiting**: Configurable cooldown between requests
- **Max attempts**: Automatic failure after N attempts
- **Expiry**: Time-based expiration of codes
- **Status tracking**: Comprehensive audit trail

## Verification Types

Supported verification types (extensible):

- `email` - Email address verification
- `phone` - Phone number verification  
- `sms` - SMS-based verification
- `two_factor` - 2FA codes
- `password_reset` - Password reset tokens
- Custom types as needed

## Typical Flow

1. **Request Code**: User requests verification → System generates OTP → Cancel pending verifications → Create new record → Send OTP via email/SMS
2. **User Receives**: User receives code in email/SMS
3. **Submit Code**: User submits code via API
4. **Verify**: System validates code → Check expiry → Check attempts → Compare hash → Update status
5. **Update Entity**: Mark user field as verified (email_verified, phone_verified, etc.)

## Maintenance

### Cleanup Cron Job

Add to `billoria_accounts.module`:

```php
/**
 * Implements hook_cron().
 */
function billoria_accounts_cron() {
  $verification_service = \Drupal::service('billoria_accounts.user_verification');
  $deleted = $verification_service->cleanupOldRecords(30);
  
  if ($deleted > 0) {
    \Drupal::logger('billoria_accounts')->info('Cleaned up @count old verification records.', ['@count' => $deleted]);
  }
}
```

## Benefits Over User Fields

✅ **Reusable** - One table for all verification types  
✅ **Scalable** - Indexed queries, easy cleanup  
✅ **Secure** - Hashed codes, rate limiting, audit trail  
✅ **Flexible** - Metadata field for custom data  
✅ **Maintainable** - Centralized service, no entity field pollution  
✅ **Testable** - Easy to mock and unit test
