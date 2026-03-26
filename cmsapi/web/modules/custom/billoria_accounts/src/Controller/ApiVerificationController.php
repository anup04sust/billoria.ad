<?php

namespace Drupal\billoria_accounts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST API controller for verification endpoints.
 *
 * Provides JSON API for Next.js frontend.
 */
class ApiVerificationController extends ControllerBase {

  /**
   * Verify email with token.
   *
   * POST /api/verify-email
   *
   * Expected JSON:
   * {
   *   "uid": 123,
   *   "token": "abc123..."
   * }
   */
  public function verifyEmail(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    if (!$data || empty($data['uid']) || empty($data['token'])) {
      return new JsonResponse(['error' => 'Missing uid or token'], 400);
    }

    $user = User::load($data['uid']);

    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    // Check if already verified
    if ($user->get('field_email_verified')->value) {
      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Email already verified',
        'data' => ['emailVerified' => TRUE],
      ]);
    }

    $stored_token = $user->get('field_verification_token')->value;
    $token_expiry = $user->get('field_token_expiry')->value;

    // Validate token
    if ($data['token'] !== $stored_token) {
      return new JsonResponse(['error' => 'Invalid verification token'], 401);
    }

    // Check if expired
    if (time() > $token_expiry) {
      return new JsonResponse(['error' => 'Verification token expired'], 401);
    }

    // Verify email
    $user->set('field_email_verified', TRUE);
    $user->set('status', 1); // Activate account
    $user->save();

    // Update organization verification status
    $org_refs = $user->get('field_organization')->referencedEntities();
    if (!empty($org_refs)) {
      $organization = reset($org_refs);
      $organization->set('field_verification_status', 'email_verified');

      // Recalculate trust score
      $trust_score = 50 + 10; // Base 50 + 10 for email verification
      $organization->set('field_trust_score', $trust_score);

      // Update profile completion
      $completion = $this->calculateProfileCompletion($organization);
      $organization->set('field_profile_completion', $completion);

      $organization->save();
    }

    \Drupal::logger('billoria_accounts')->notice('Email verified for user @uid', [
      '@uid' => $user->id(),
    ]);

    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Email verified successfully',
      'data' => [
        'userId' => (int) $user->id(),
        'emailVerified' => TRUE,
        'trustScore' => $trust_score ?? 60,
      ],
    ]);
  }

  /**
   * Verify phone with OTP.
   *
   * POST /api/verify-phone
   *
   * Expected JSON:
   * {
   *   "otp": "123456"
   * }
   */
  public function verifyPhone(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    if (!$data || empty($data['otp'])) {
      return new JsonResponse(['error' => 'Missing OTP'], 400);
    }

    $user = User::load($this->currentUser()->id());

    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    // Check if already verified
    if ($user->get('field_phone_verified')->value) {
      return new JsonResponse([
        'success' => TRUE,
        'message' => 'Phone already verified',
        'data' => ['phoneVerified' => TRUE],
      ]);
    }

    $stored_otp = $user->get('field_phone_otp')->value;
    $otp_expiry = $user->get('field_phone_otp_expiry')->value;

    // Validate OTP
    if ($data['otp'] !== $stored_otp) {
      return new JsonResponse(['error' => 'Invalid OTP'], 401);
    }

    // Check if expired (10 minutes)
    if (time() > $otp_expiry) {
      return new JsonResponse(['error' => 'OTP expired. Please request a new one.'], 401);
    }

    // Verify phone
    $user->set('field_phone_verified', TRUE);
    $user->set('field_phone_otp', NULL); // Clear OTP
    $user->save();

    // Update organization trust score
    $org_refs = $user->get('field_organization')->referencedEntities();
    if (!empty($org_refs)) {
      $organization = reset($org_refs);

      // Increase trust score
      $current_trust = $organization->get('field_trust_score')->value ?? 50;
      $organization->set('field_trust_score', min($current_trust + 15, 100)); // +15 for phone verification

      // Update profile completion
      $completion = $this->calculateProfileCompletion($organization);
      $organization->set('field_profile_completion', $completion);

      $organization->save();
    }

    \Drupal::logger('billoria_accounts')->notice('Phone verified for user @uid', [
      '@uid' => $user->id(),
    ]);

    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Phone verified successfully',
      'data' => [
        'phoneVerified' => TRUE,
        'trustScore' => $organization->get('field_trust_score')->value ?? 65,
      ],
    ]);
  }

  /**
   * Resend verification email.
   *
   * POST /api/resend-verification
   */
  public function resendVerification(Request $request) {
    $user = User::load($this->currentUser()->id());

    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    if ($user->get('field_email_verified')->value) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Email already verified',
      ], 400);
    }

    // Generate new token
    $token = bin2hex(random_bytes(16));
    $expiry = time() + 3600; // 1 hour

    $user->set('field_verification_token', $token);
    $user->set('field_token_expiry', $expiry);
    $user->save();

    // Send email
    $frontend_url = getenv('FRONTEND_URL') ?: 'http://localhost:3000';
    $verification_url = $frontend_url . '/verify-email?uid=' . $user->id() . '&token=' . $token;

    $params = [
      'subject' => 'Verify your email for Billoria',
      'body' => "Hello " . $user->getAccountName() . ",\n\n"
        . "Please verify your email address by clicking the link below:\n\n"
        . $verification_url . "\n\n"
        . "Or enter this code: " . $token . "\n\n"
        . "This link will expire in 1 hour.\n\n"
        . "Best regards,\nBilloria Team",
    ];

    $mailManager = \Drupal::service('plugin.manager.mail');
    $mailManager->mail('billoria_accounts', 'verify_email', $user->getEmail(), 'en', $params, NULL, TRUE);

    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Verification email sent',
    ]);
  }

  /**
   * Request phone OTP.
   *
   * POST /api/request-phone-otp
   */
  public function requestPhoneOtp(Request $request) {
    $user = User::load($this->currentUser()->id());

    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    $mobile = $user->get('field_mobile_number')->value;

    if (!$mobile) {
      return new JsonResponse(['error' => 'No mobile number on file'], 400);
    }

    // Generate 6-digit OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = time() + 600; // 10 minutes

    $user->set('field_phone_otp', $otp);
    $user->set('field_phone_otp_expiry', $expiry);
    $user->save();

    // Send SMS (placeholder - integrate with BD SMS gateway)
    $this->sendOtpSms($mobile, $otp);

    \Drupal::logger('billoria_accounts')->notice('OTP sent to @mobile for user @uid', [
      '@mobile' => $mobile,
      '@uid' => $user->id(),
    ]);

    return new JsonResponse([
      'success' => TRUE,
      'message' => 'OTP sent to your mobile number',
      'data' => [
        'mobile' => substr($mobile, 0, -4) . 'XXXX', // Masked for security
        'expiresIn' => 600, // seconds
      ],
    ]);
  }

  /**
   * Send OTP via SMS.
   */
  protected function sendOtpSms($mobile, $otp) {
    // TODO: Integrate with BD SMS gateway (BD-SMS, SSL Wireless, etc.)
    // For MVP, log the OTP
    \Drupal::logger('billoria_accounts')->info('SMS OTP for @mobile: @otp', [
      '@mobile' => $mobile,
      '@otp' => $otp,
    ]);

    // Example integration:
    // $sms_gateway = \Drupal::service('billoria_core.sms_gateway');
    // $sms_gateway->send($mobile, "Your Billoria verification code is: $otp. Valid for 10 minutes.");
  }

  /**
   * Calculate profile completion percentage.
   */
  protected function calculateProfileCompletion($organization) {
    $total_fields = 0;
    $completed_fields = 0;

    $org_type = $organization->get('field_org_type')->value;

    // Common required fields
    $common_fields = [
      'title', 'field_official_email', 'field_official_phone',
      'field_division', 'field_district', 'field_full_address',
    ];

    foreach ($common_fields as $field_name) {
      $total_fields++;
      if (!$organization->get($field_name)->isEmpty()) {
        $completed_fields++;
      }
    }

    // Optional but recommended fields
    $optional_fields = [
      'field_website', 'field_business_reg_number', 'field_tin',
      'field_org_logo', 'field_establishment_year',
    ];

    foreach ($optional_fields as $field_name) {
      $total_fields++;
      if (!$organization->get($field_name)->isEmpty()) {
        $completed_fields++;
      }
    }

    // Type-specific fields
    if ($org_type === 'brand') {
      $type_fields = ['field_annual_budget_range', 'field_preferred_regions'];
    }
    elseif ($org_type === 'agency') {
      $type_fields = ['field_agency_services', 'field_portfolio_size'];
    }
    elseif ($org_type === 'owner') {
      $type_fields = ['field_inventory_count', 'field_coverage_districts'];
    }

    if (isset($type_fields)) {
      foreach ($type_fields as $field_name) {
        $total_fields++;
        if (!$organization->get($field_name)->isEmpty()) {
          $completed_fields++;
        }
      }
    }

    return $total_fields > 0 ? round(($completed_fields / $total_fields) * 100) : 0;
  }

}
