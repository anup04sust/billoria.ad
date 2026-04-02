<?php

namespace Drupal\billoria_accounts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\billoria_accounts\Service\UserVerificationService;

/**
 * REST API controller for verification endpoints.
 *
 * Provides JSON API for Next.js frontend.
 */
class ApiVerificationController extends ControllerBase {

  /**
   * The user verification service.
   *
   * @var \Drupal\billoria_accounts\Service\UserVerificationService
   */
  protected $verificationService;

  /**
   * Constructs an ApiVerificationController object.
   *
   * @param \Drupal\billoria_accounts\Service\UserVerificationService $verification_service
   *   The verification service.
   */
  public function __construct(UserVerificationService $verification_service) {
    $this->verificationService = $verification_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('billoria_accounts.user_verification')
    );
  }

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
    // Check if billoria_sms module is installed and service is available
    if (!\Drupal::hasService('billoria_sms.sender')) {
      // Fallback: Log OTP if SMS module not installed (development mode)
      \Drupal::logger('billoria_accounts')->warning('SMS service not available. Install billoria_sms module. OTP for @mobile: @otp', [
        '@mobile' => $mobile,
        '@otp' => $otp,
      ]);
      return;
    }

    // Integrate with Alpha SMS gateway via billoria_sms module
    $sms_sender = \Drupal::service('billoria_sms.sender');
    
    if (!$sms_sender->isConfigured()) {
      // Fallback: Log OTP if SMS not configured (development mode)
      \Drupal::logger('billoria_accounts')->warning('SMS service not configured. OTP for @mobile: @otp', [
        '@mobile' => $mobile,
        '@otp' => $otp,
      ]);
      return;
    }

    // Send OTP via Alpha SMS
    $result = $sms_sender->sendOtp($mobile, $otp, 10);
    
    if ($result['success']) {
      \Drupal::logger('billoria_accounts')->info('SMS OTP sent to @mobile. Request ID: @id', [
        '@mobile' => $mobile,
        '@id' => $result['request_id'] ?? 'N/A',
      ]);
    }
    else {
      \Drupal::logger('billoria_accounts')->error('Failed to send SMS OTP to @mobile: @error', [
        '@mobile' => $mobile,
        '@error' => $result['message'],
      ]);
    }
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

  /**
   * Send email OTP verification code.
   *
   * POST /api/v1/verification/email/send-otp
   *
   * Response:
   * {
   *   "success": true,
   *   "message": "Verification code sent to your email",
   *   "data": {
   *     "email": "user@example.com",
   *     "expiresIn": 600
   *   }
   * }
   */
  public function sendEmailOtp(Request $request) {
    $uid = $this->currentUser()->id();

    if (!$uid) {
      return new JsonResponse(['error' => 'Authentication required'], 401);
    }

    $user = User::load($uid);
    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    $email = $user->getEmail();
    if (!$email) {
      return new JsonResponse(['error' => 'No email address on file'], 400);
    }

    // Check if already verified
    if ($user->get('field_email_verified')->value) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Email already verified',
      ], 400);
    }

    // Rate limiting check (60 second cooldown)
    $rate_check = $this->verificationService->canRequestNewCode($uid, 'email', 60);
    if (!$rate_check['allowed']) {
      return new JsonResponse([
        'error' => 'rate_limit',
        'message' => $rate_check['message'],
        'retryAfter' => $rate_check['retry_after'],
      ], 429);
    }

    // Generate OTP and create verification record
    $otp = $this->verificationService->generateOtpCode(6);

    // Cancel any pending verifications
    $this->verificationService->cancelPendingVerifications($uid, 'email');

    // Create new verification
    $verification_id = $this->verificationService->createVerification(
      $uid,
      'email',
      $email,
      $otp,
      600, // 10 minutes
      5,   // 5 max attempts
      [
        'ip' => $request->getClientIp(),
        'user_agent' => $request->headers->get('User-Agent'),
      ]
    );

    if (!$verification_id) {
      return new JsonResponse(['error' => 'Failed to create verification record'], 500);
    }

    // Send email
    $this->sendOtpEmail($user, $otp);

    \Drupal::logger('billoria_accounts')->notice('Email OTP sent to user @uid (@email)', [
      '@uid' => $uid,
      '@email' => $email,
    ]);

    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Verification code sent to your email',
      'data' => [
        'email' => $this->maskEmail($email),
        'expiresIn' => 600,
      ],
    ]);
  }

  /**
   * Verify email OTP code.
   *
   * POST /api/v1/verification/email/verify-otp
   *
   * Expected JSON:
   * {
   *   "code": "123456"
   * }
   *
   * Response:
   * {
   *   "success": true,
   *   "message": "Email verified successfully",
   *   "data": {
   *     "emailVerified": true,
   *     "trustScore": 60
   *   }
   * }
   */
  public function verifyEmailOtp(Request $request) {
    $uid = $this->currentUser()->id();

    if (!$uid) {
      return new JsonResponse(['error' => 'Authentication required'], 401);
    }

    $data = json_decode($request->getContent(), TRUE);
    $code = $data['code'] ?? '';

    if (empty($code)) {
      return new JsonResponse(['error' => 'Verification code is required'], 400);
    }

    if (!preg_match('/^\d{6}$/', $code)) {
      return new JsonResponse(['error' => 'Invalid code format. Expected 6 digits.'], 400);
    }

    // Get latest pending verification
    $latest = $this->verificationService->getLatestVerification($uid, 'email');

    if (!$latest) {
      return new JsonResponse(['error' => 'No pending verification found. Please request a new code.'], 404);
    }

    // Verify the code
    $result = $this->verificationService->verifyCode($latest->id, $code);

    if (!$result['success']) {
      return new JsonResponse($result, 400);
    }

    // Mark email as verified in user entity
    $user = User::load($uid);
    $user->set('field_email_verified', TRUE);
    $user->save();

    // Update organization trust score
    $org_refs = $user->get('field_organization')->referencedEntities();
    if (!empty($org_refs)) {
      $organization = reset($org_refs);

      // Increase trust score
      $current_trust = $organization->get('field_trust_score')->value ?? 50;
      $new_trust = min($current_trust + 10, 100); // +10 for email verification
      $organization->set('field_trust_score', $new_trust);

      // Update profile completion
      $completion = $this->calculateProfileCompletion($organization);
      $organization->set('field_profile_completion', $completion);

      $organization->save();

      $result['data']['trustScore'] = $new_trust;
    }

    $result['data']['emailVerified'] = TRUE;

    \Drupal::logger('billoria_accounts')->notice('Email verified via OTP for user @uid', [
      '@uid' => $uid,
    ]);

    return new JsonResponse($result);
  }

  /**
   * Send phone OTP verification code.
   *
   * POST /api/v1/verification/phone/send-otp
   */
  public function sendPhoneOtp(Request $request) {
    $uid = $this->currentUser()->id();

    if (!$uid) {
      return new JsonResponse(['error' => 'Authentication required'], 401);
    }

    $user = User::load($uid);
    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    $phone = $user->get('field_mobile_number')->value;
    if (!$phone) {
      return new JsonResponse(['error' => 'No mobile number on file'], 400);
    }

    // Check if already verified
    if ($user->get('field_phone_verified')->value) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Phone already verified',
      ], 400);
    }

    // Rate limiting check (60 second cooldown)
    $rate_check = $this->verificationService->canRequestNewCode($uid, 'phone', 60);
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
    $this->verificationService->cancelPendingVerifications($uid, 'phone');

    $verification_id = $this->verificationService->createVerification(
      $uid,
      'phone',
      $phone,
      $otp,
      300, // 5 minutes (shorter for SMS)
      3,   // 3 max attempts (stricter for SMS)
      [
        'ip' => $request->getClientIp(),
        'user_agent' => $request->headers->get('User-Agent'),
      ]
    );

    if (!$verification_id) {
      return new JsonResponse(['error' => 'Failed to create verification record'], 500);
    }

    // Send SMS
    $this->sendOtpSms($phone, $otp);

    \Drupal::logger('billoria_accounts')->notice('Phone OTP sent to user @uid (@phone)', [
      '@uid' => $uid,
      '@phone' => $phone,
    ]);

    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Verification code sent to your phone',
      'data' => [
        'phone' => substr($phone, 0, -4) . 'XXXX',
        'expiresIn' => 300,
      ],
    ]);
  }

  /**
   * Verify phone OTP code.
   *
   * POST /api/v1/verification/phone/verify-otp
   *
   * Expected JSON:
   * {
   *   "code": "123456"
   * }
   */
  public function verifyPhoneOtp(Request $request) {
    $uid = $this->currentUser()->id();

    if (!$uid) {
      return new JsonResponse(['error' => 'Authentication required'], 401);
    }

    $data = json_decode($request->getContent(), TRUE);
    $code = $data['code'] ?? '';

    if (empty($code)) {
      return new JsonResponse(['error' => 'Verification code is required'], 400);
    }

    if (!preg_match('/^\d{6}$/', $code)) {
      return new JsonResponse(['error' => 'Invalid code format. Expected 6 digits.'], 400);
    }

    // Get latest pending verification
    $latest = $this->verificationService->getLatestVerification($uid, 'phone');

    if (!$latest) {
      return new JsonResponse(['error' => 'No pending verification found. Please request a new code.'], 404);
    }

    // Verify the code
    $result = $this->verificationService->verifyCode($latest->id, $code);

    if (!$result['success']) {
      return new JsonResponse($result, 400);
    }

    // Mark phone as verified in user entity
    $user = User::load($uid);
    $user->set('field_phone_verified', TRUE);
    $user->save();

    // Update organization trust score
    $org_refs = $user->get('field_organization')->referencedEntities();
    if (!empty($org_refs)) {
      $organization = reset($org_refs);

      // Increase trust score
      $current_trust = $organization->get('field_trust_score')->value ?? 50;
      $new_trust = min($current_trust + 15, 100); // +15 for phone verification
      $organization->set('field_trust_score', $new_trust);

      // Update profile completion
      $completion = $this->calculateProfileCompletion($organization);
      $organization->set('field_profile_completion', $completion);

      $organization->save();

      $result['data']['trustScore'] = $new_trust;
    }

    $result['data']['phoneVerified'] = TRUE;

    \Drupal::logger('billoria_accounts')->notice('Phone verified via OTP for user @uid', [
      '@uid' => $uid,
    ]);

    return new JsonResponse($result);
  }

  /**
   * Get verification status for current user.
   *
   * GET /api/v1/verification/status
   *
   * Response:
   * {
   *   "success": true,
   *   "data": {
   *     "email": {
   *       "verified": true,
   *       "hasPending": false
   *     },
   *     "phone": {
   *       "verified": false,
   *       "hasPending": true,
   *       "expiresAt": 1234567890
   *     }
   *   }
   * }
   */
  public function getVerificationStatus(Request $request) {
    $uid = $this->currentUser()->id();

    if (!$uid) {
      return new JsonResponse(['error' => 'Authentication required'], 401);
    }

    $user = User::load($uid);
    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    // Email status
    $email_verified = (bool) $user->get('field_email_verified')->value;
    $email_pending = $this->verificationService->getLatestVerification($uid, 'email');

    // Phone status
    $phone_verified = (bool) $user->get('field_phone_verified')->value;
    $phone_pending = $this->verificationService->getLatestVerification($uid, 'phone');

    return new JsonResponse([
      'success' => TRUE,
      'data' => [
        'email' => [
          'verified' => $email_verified,
          'hasPending' => $email_pending !== NULL,
          'expiresAt' => $email_pending ? $email_pending->expires : NULL,
        ],
        'phone' => [
          'verified' => $phone_verified,
          'hasPending' => $phone_pending !== NULL,
          'expiresAt' => $phone_pending ? $phone_pending->expires : NULL,
        ],
      ],
    ]);
  }

  /**
   * Send OTP via email.
   */
  protected function sendOtpEmail($user, $otp) {
    $mailManager = \Drupal::service('plugin.manager.mail');
    
    // Get base64 encoded logo
    $logo_base64 = $this->getLogoBase64();
    
    // Prepare template variables
    $variables = [
      'user_name' => $user->getAccountName(),
      'otp_code' => $otp,
      'expiry_minutes' => 10,
      'logo_base64' => $logo_base64,
      'help_url' => 'https://billoria.ad/help',
      'privacy_url' => 'https://billoria.ad/privacy',
      'unsubscribe_url' => 'https://billoria.ad/unsubscribe',
      'current_year' => date('Y'),
    ];

    $params = [
      'subject' => 'Your Billoria Verification Code',
      'user' => $user,
      'otp' => $otp,
      'variables' => $variables,
    ];

    $result = $mailManager->mail(
      'billoria_accounts',
      'otp_verification',
      $user->getEmail(),
      'en',
      $params,
      NULL,
      TRUE
    );

    return $result['result'];
  }

  /**
   * Get base64 encoded logo for email.
   */
  protected function getLogoBase64() {
    // Check for logo file in public files
    $file_system = \Drupal::service('file_system');
    $public_path = \Drupal::service('file_system')->realpath('public://');
    
    // Try common logo locations
    $logo_paths = [
      $public_path . '/logo.png',
      $public_path . '/billoria-logo.png',
      DRUPAL_ROOT . '/themes/custom/billoria/logo.png',
      DRUPAL_ROOT . '/logo.png',
    ];
    
    foreach ($logo_paths as $path) {
      if (file_exists($path)) {
        $image_data = file_get_contents($path);
        $image_type = mime_content_type($path);
        return 'data:' . $image_type . ';base64,' . base64_encode($image_data);
      }
    }
    
    // Fallback: Return inline SVG logo
    return $this->getBilloriaSvgLogo();
  }

  /**
   * Get Billoria SVG logo as base64 data URI.
   */
  protected function getBilloriaSvgLogo() {
    // Billoria brand SVG logo
    $svg = <<<SVG
<svg width="150" height="40" viewBox="0 0 150 40" xmlns="http://www.w3.org/2000/svg">
  <!-- Background -->
  <rect width="150" height="40" rx="4" fill="#1e40af"/>
  
  <!-- Icon: Billboard -->
  <g transform="translate(10, 8)">
    <rect x="2" y="4" width="16" height="10" rx="1" fill="#ffffff" opacity="0.9"/>
    <rect x="4" y="6" width="12" height="6" fill="#60a5fa"/>
    <rect x="9" y="14" width="2" height="8" fill="#ffffff" opacity="0.8"/>
    <rect x="6" y="22" width="8" height="2" rx="1" fill="#ffffff" opacity="0.8"/>
  </g>
  
  <!-- Text: BILLORIA -->
  <text x="35" y="26" font-family="Arial, sans-serif" font-size="16" font-weight="700" fill="#ffffff">
    BILLORIA
  </text>
  
  <!-- Tagline dot -->
  <circle cx="130" cy="24" r="2" fill="#fbbf24"/>
</svg>
SVG;
    
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
  }

  /**
   * Mask email address for privacy.
   */
  protected function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
      return $email;
    }

    $local = $parts[0];
    $domain = $parts[1];

    if (strlen($local) <= 2) {
      return '*' . substr($local, -1) . '@' . $domain;
    }

    $masked_local = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 3)) . substr($local, -1);
    return $masked_local . '@' . $domain;
  }

}

