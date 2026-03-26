<?php

namespace Drupal\billoria_accounts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordInterface;

/**
 * REST API controller for registration endpoints.
 *
 * Provides JSON API for Next.js frontend.
 */
class ApiRegistrationController extends ControllerBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Password hasher.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordHasher;

  /**
   * Constructs a new ApiRegistrationController.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    PasswordInterface $password_hasher
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->passwordHasher = $password_hasher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('password')
    );
  }

  /**
   * Register new user with organization.
   *
   * POST /api/v1/register
   *
   * Expected JSON payload:
   * {
   *   "accountType": "brand|agency|owner",
   *   "user": {
   *     "name": "John Doe",
   *     "email": "john@example.com",
   *     "password": "securepass123",
   *     "mobileNumber": "+8801712345678"
   *   },
   *   "organization": {
   *     "name": "Acme Corp",
   *     "officialEmail": "info@acme.com",
   *     "officialPhone": "+8801712345678",
   *     "website": "https://acme.com",
   *     "division": 123,
   *     "district": 456,
   *     "fullAddress": "123 Main St, Dhaka",
   *     "businessRegNumber": "REG123",
   *     "tin": "TIN456",
   *     ...type-specific fields
   *   }
   * }
   *
   * Security: CSRF validation and rate limiting handled by BilloriaCoreSubscriber middleware.
   */
  public function register(Request $request) {
    $data = json_decode($request->getContent(), TRUE);

    if (!$data) {
      return new JsonResponse(['error' => 'Invalid JSON'], 400);
    }

    // Validate required fields
    $required = ['accountType', 'user', 'organization'];
    foreach ($required as $field) {
      if (empty($data[$field])) {
        return new JsonResponse(['error' => "Missing required field: $field"], 400);
      }
    }

    $account_type = $data['accountType'];
    if (!in_array($account_type, ['brand', 'agency', 'owner'])) {
      return new JsonResponse(['error' => 'Invalid account type'], 400);
    }

    // Validate user data
    $user_data = $data['user'];
    if (empty($user_data['email']) || empty($user_data['password']) || empty($user_data['name'])) {
      return new JsonResponse(['error' => 'Missing user credentials'], 400);
    }

    // Check if email already exists
    $existing_users = $this->entityTypeManager->getStorage('user')->loadByProperties([
      'mail' => $user_data['email'],
    ]);
    if (!empty($existing_users)) {
      return new JsonResponse(['error' => 'Email already registered'], 409);
    }

    // Validate mobile number format
    $mobile = $user_data['mobileNumber'] ?? '';
    if (!preg_match('/^\+880\d{10}$/', $mobile)) {
      return new JsonResponse(['error' => 'Invalid mobile number format. Use +8801XXXXXXXXX'], 400);
    }

    try {
      // Create user account
      $user = User::create([
        'name' => $user_data['email'],
        'mail' => $user_data['email'],
        'pass' => $user_data['password'],
        'status' => 0, // Inactive until email verified
        'field_mobile_number' => $mobile,
        'field_email_verified' => FALSE,
        'field_phone_verified' => FALSE,
      ]);

      // Generate email verification token
      $token = bin2hex(random_bytes(16));
      $expiry = time() + 3600; // 1 hour

      $user->set('field_verification_token', $token);
      $user->set('field_token_expiry', $expiry);
      $user->save();

      // Create organization node
      $org_data = $data['organization'];
      $organization = $this->createOrganization($account_type, $org_data, $user);

      // Link organization to user
      $user->set('field_organization', [$organization->id()]);
      $user->set('field_active_organization', $organization->id());
      $user->set('field_is_primary_admin', TRUE);
      $user->save();

      // Assign role based on account type
      $role_map = [
        'brand' => 'brand_user',
        'agency' => 'agency',
        'owner' => 'billboard_owner',
      ];
      $user->addRole($role_map[$account_type]);
      $user->save();

      // Send verification email
      $this->sendVerificationEmail($user, $token);

      // Return success response
      $response = new JsonResponse([
        'success' => TRUE,
        'message' => 'Registration successful. Please check your email for verification.',
        'data' => [
          'userId' => (int) $user->id(),
          'organizationId' => (int) $organization->id(),
          'email' => $user->getEmail(),
          'verificationRequired' => TRUE,
        ],
      ], 201);

      return $response;

    } catch (\Exception $e) {
      \Drupal::logger('billoria_accounts')->error('Registration failed: @message', [
        '@message' => $e->getMessage(),
      ]);

      return new JsonResponse([
        'error' => 'Registration failed. Please try again.',
        'details' => $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Create organization node based on account type.
   */
  protected function createOrganization($account_type, $org_data, User $user) {
    // Common fields for all types
    $node_data = [
      'type' => 'organization',
      'title' => $org_data['name'],
      'field_org_type' => $account_type,
      'field_official_email' => $org_data['officialEmail'] ?? '',
      'field_official_phone' => $org_data['officialPhone'] ?? '',
      'field_website' => !empty($org_data['website']) ? ['uri' => $org_data['website']] : NULL,
      'field_division' => $org_data['division'] ?? NULL,
      'field_district' => $org_data['district'] ?? NULL,
      'field_city_corporation' => $org_data['cityCorporation'] ?? NULL,
      'field_full_address' => $org_data['fullAddress'] ?? '',
      'field_postal_code' => $org_data['postalCode'] ?? '',
      'field_business_reg_number' => $org_data['businessRegNumber'] ?? '',
      'field_tin' => $org_data['tin'] ?? '',
      'field_establishment_year' => $org_data['establishmentYear'] ?? NULL,
      'field_primary_admin' => $user->id(),
      'field_verification_status' => 'draft',
      'field_trust_score' => 50,
      'field_profile_completion' => 30, // Initial completion
      'status' => 1,
    ];

    // Add type-specific fields
    if ($account_type === 'brand') {
      $node_data['field_parent_company'] = $org_data['parentCompany'] ?? '';
      $node_data['field_annual_budget_range'] = $org_data['annualBudgetRange'] ?? '';
      $node_data['field_booking_duration'] = $org_data['bookingDuration'] ?? '';
      $node_data['field_preferred_regions'] = $org_data['preferredRegions'] ?? [];
    }
    elseif ($account_type === 'agency') {
      $node_data['field_agency_services'] = $org_data['agencyServices'] ?? [];
      $node_data['field_portfolio_size'] = $org_data['portfolioSize'] ?? '';
      $node_data['field_owns_inventory'] = $org_data['ownsInventory'] ?? FALSE;
      $node_data['field_operations_contact'] = $org_data['operationsContact'] ?? '';
      $node_data['field_finance_contact'] = $org_data['financeContact'] ?? '';
      $node_data['field_preferred_regions'] = $org_data['preferredRegions'] ?? [];
    }
    elseif ($account_type === 'owner') {
      $node_data['field_inventory_count'] = $org_data['inventoryCount'] ?? 0;
      $node_data['field_maintenance_capability'] = $org_data['maintenanceCapability'] ?? '';
      $node_data['field_installation_services'] = $org_data['installationServices'] ?? FALSE;
      $node_data['field_coverage_districts'] = $org_data['coverageDistricts'] ?? [];
    }

    $organization = Node::create($node_data);
    $organization->save();

    return $organization;
  }

  /**
   * Send verification email.
   */
  protected function sendVerificationEmail(User $user, $token) {
    // Use frontend URL for verification link
    $frontend_url = getenv('FRONTEND_URL') ?: 'http://localhost:3000';
    $verification_url = $frontend_url . '/verify-email?uid=' . $user->id() . '&token=' . $token;

    $params = [
      'subject' => 'Verify your email for Billoria',
      'body' => "Hello " . $user->getAccountName() . ",\n\n"
        . "Welcome to Billoria! Please verify your email address by clicking the link below:\n\n"
        . $verification_url . "\n\n"
        . "Or enter this verification code: " . $token . "\n\n"
        . "This link will expire in 1 hour.\n\n"
        . "If you didn't create this account, please ignore this email.\n\n"
        . "Best regards,\nBilloria Team",
    ];

    $mailManager = \Drupal::service('plugin.manager.mail');
    $mailManager->mail('billoria_accounts', 'verify_email', $user->getEmail(), 'en', $params, NULL, TRUE);

    \Drupal::logger('billoria_accounts')->notice('Verification email sent to @email', [
      '@email' => $user->getEmail(),
    ]);
  }

}
