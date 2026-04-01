<?php

namespace Drupal\billoria_accounts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST API controller for user profile and organisation data.
 *
 * All routes require _user_is_logged_in: TRUE in routing.yml.
 * The controller also performs explicit auth + ownership checks so the
 * security does not depend solely on routing configuration.
 */
class ApiProfileController extends ControllerBase {

  // ── Helpers ──────────────────────────────────────────────────────────────

  /**
   * Returns a 401 response — should never be reached if routing is correct,
   * but acts as a hard defence-in-depth guard.
   */
  private function unauthorized(string $msg = 'Authentication required.'): JsonResponse {
    return new JsonResponse(['success' => false, 'message' => $msg], 401);
  }

  /**
   * Returns a 403 response.
   */
  private function forbidden(string $msg = 'Access denied.'): JsonResponse {
    return new JsonResponse(['success' => false, 'message' => $msg], 403);
  }

  /**
   * Resolve a taxonomy term to {id, name} or null.
   */
  private function termRef(?object $ref): ?array {
    if (!$ref || !$ref->target_id) {
      return NULL;
    }
    $term = Term::load($ref->target_id);
    return $term ? ['id' => (int) $term->id(), 'name' => $term->label()] : NULL;
  }

  /**
   * Resolve a multi-value term reference field to [{id, name}, ...].
   */
  private function termRefs(object $field): array {
    $result = [];
    foreach ($field->getValue() as $item) {
      $term = Term::load($item['target_id']);
      if ($term) {
        $result[] = ['id' => (int) $term->id(), 'name' => $term->label()];
      }
    }
    return $result;
  }

  // ── GET /api/v1/user/profile ──────────────────────────────────────────────

  /**
   * Returns the authenticated user's full profile + all linked organisations.
   *
   * Response shape (matches frontend profileAPI types):
   * {
   *   "success": true,
   *   "data": {
   *     "user": { … },
   *     "organizations": [ { … }, … ]
   *   }
   * }
   */
  public function getProfile(): JsonResponse {
    // ── Defence-in-depth auth check ─────────────────────────────────────────
    $current = $this->currentUser();
    if ($current->isAnonymous()) {
      return $this->unauthorized();
    }

    // Verify the account still exists and is active.
    $user = User::load($current->id());
    if (!$user || !$user->isActive()) {
      return $this->unauthorized('Your account is inactive or has been removed.');
    }

    // ── Collect organisations ────────────────────────────────────────────────
    $organizations = [];

    // Primary: field_active_organization (single reference).
    $active_org = $user->hasField('field_active_organization')
      ? $user->get('field_active_organization')->entity
      : NULL;

    // Additional: field_organization (multi-value).
    $all_org_ids = [];
    if ($user->hasField('field_organization')) {
      $all_org_ids = array_column($user->get('field_organization')->getValue(), 'target_id');
    }
    if ($active_org && !in_array((string) $active_org->id(), array_map('strval', $all_org_ids))) {
      $all_org_ids[] = $active_org->id();
    }

    foreach (array_unique($all_org_ids) as $nid) {
      $org = Node::load($nid);
      if ($org && $org->bundle() === 'organization' && $org->access('view', $current)) {
        $is_active = $active_org && (int) $active_org->id() === (int) $org->id();
        $organizations[] = $this->serializeOrganization($org, $is_active);
      }
    }

    // If active org wasn't in the multi-value field, add it alone.
    if (empty($organizations) && $active_org) {
      $organizations[] = $this->serializeOrganization($active_org, TRUE);
    }

    return new JsonResponse([
      'success' => TRUE,
      'data' => [
        'user'          => $this->serializeUser($user),
        'organizations' => $organizations,
      ],
    ]);
  }

  // ── GET /api/v1/organization/{nid}/status ────────────────────────────────

  /**
   * Returns status/stats for a specific organisation the current user owns.
   */
  public function getOrganizationStatus(int $nid): JsonResponse {
    // Auth guard.
    $current = $this->currentUser();
    if ($current->isAnonymous()) {
      return $this->unauthorized();
    }

    $user = User::load($current->id());
    if (!$user || !$user->isActive()) {
      return $this->unauthorized('Your account is inactive.');
    }

    $org = Node::load($nid);
    if (!$org || $org->bundle() !== 'organization') {
      return new JsonResponse(['success' => FALSE, 'message' => 'Organization not found.'], 404);
    }

    // Ownership check — user must have this org in field_organization or field_active_organization.
    $linked_ids = [];
    if ($user->hasField('field_organization')) {
      $linked_ids = array_column($user->get('field_organization')->getValue(), 'target_id');
    }
    if ($user->hasField('field_active_organization')) {
      $active = $user->get('field_active_organization')->target_id;
      if ($active) {
        $linked_ids[] = $active;
      }
    }

    if (!in_array((string) $nid, array_map('strval', $linked_ids))) {
      return $this->forbidden('You do not have access to this organisation.');
    }

    // Additionally check Drupal node access.
    if (!$org->access('view', $current)) {
      return $this->forbidden();
    }

    $org_type           = $org->get('field_org_type')->value;
    $verification_status = $org->get('field_verification_status')->value ?? 'pending';
    $trust_score        = (int) ($org->get('field_trust_score')->value ?? 50);
    $profile_completion = (int) ($org->get('field_profile_completion')->value ?? 0);

    $response = [
      'success' => TRUE,
      'data' => [
        'id'                 => (int) $org->id(),
        'name'               => $org->getTitle(),
        'type'               => $org_type,
        'verificationStatus' => $verification_status,
        'trustScore'         => $trust_score,
        'profileCompletion'  => $profile_completion,
        'stats'              => [],
      ],
    ];

    if ($org_type === 'owner') {
      $response['data']['stats']['inventoryCount'] = (int) ($org->get('field_inventory_count')->value ?? 0);
      $response['data']['stats']['coverageSqft']   = (float) ($org->get('field_total_coverage_sqft')->value ?? 0);
    }
    elseif ($org_type === 'agency') {
      $response['data']['stats']['portfolioSize']  = $org->get('field_portfolio_size')->value;
      $response['data']['stats']['ownsInventory']  = (bool) $org->get('field_owns_inventory')->value;
    }

    return new JsonResponse($response);
  }

  // ── PATCH /api/v1/user/profile ───────────────────────────────────────────

  /**
   * Updates personal fields on the authenticated user.
   *
   * Accepted body (all fields optional):
   *   mobileNumber, designation, department
   */
  public function updateUserProfile(Request $request): JsonResponse {
    $current = $this->currentUser();
    if ($current->isAnonymous()) {
      return $this->unauthorized();
    }
    $user = User::load($current->id());
    if (!$user || !$user->isActive()) {
      return $this->unauthorized('Your account is inactive.');
    }

    $body = json_decode($request->getContent(), TRUE) ?? [];

    if (array_key_exists('mobileNumber', $body) && $user->hasField('field_mobile_number')) {
      $user->set('field_mobile_number', $body['mobileNumber']);
    }
    if (array_key_exists('designation', $body) && $user->hasField('field_designation')) {
      $user->set('field_designation', $body['designation']);
    }
    if (array_key_exists('department', $body) && $user->hasField('field_department')) {
      $user->set('field_department', $body['department']);
    }

    $violations = $user->validate();
    if ($violations->count()) {
      $errors = [];
      foreach ($violations as $v) {
        $errors[] = $v->getMessage()->__toString();
      }
      return new JsonResponse(['success' => FALSE, 'message' => implode(', ', $errors)], 422);
    }

    $user->save();
    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Profile updated.',
      'data'    => ['user' => $this->serializeUser($user)],
    ]);
  }

  // ── PATCH /api/v1/organization/{nid} ─────────────────────────────────────

  /**
   * Updates an organisation node the authenticated user owns.
   */
  public function updateOrganization(int $nid, Request $request): JsonResponse {
    $current = $this->currentUser();
    if ($current->isAnonymous()) {
      return $this->unauthorized();
    }
    $user = User::load($current->id());
    if (!$user || !$user->isActive()) {
      return $this->unauthorized('Your account is inactive.');
    }

    $org = Node::load($nid);
    if (!$org || $org->bundle() !== 'organization') {
      return new JsonResponse(['success' => FALSE, 'message' => 'Organization not found.'], 404);
    }

    // Ownership check.
    $linked_ids = [];
    if ($user->hasField('field_organization')) {
      $linked_ids = array_column($user->get('field_organization')->getValue(), 'target_id');
    }
    if ($user->hasField('field_active_organization')) {
      $active = $user->get('field_active_organization')->target_id;
      if ($active) {
        $linked_ids[] = $active;
      }
    }
    if (!in_array((string) $nid, array_map('strval', $linked_ids))) {
      return $this->forbidden('You do not have access to this organisation.');
    }
    if (!$org->access('update', $current)) {
      return $this->forbidden();
    }

    $body = json_decode($request->getContent(), TRUE) ?? [];
    $field_map = [
      'officialEmail'     => 'field_official_email',
      'officialPhone'     => 'field_official_phone',
      'fullAddress'       => 'field_full_address',
      'businessRegNumber' => 'field_business_reg_number',
      'tin'               => 'field_tin',
      'establishmentYear' => 'field_establishment_year',
      'parentCompany'     => 'field_parent_company',
      'annualBudgetRange' => 'field_annual_budget_range',
      'bookingDuration'   => 'field_booking_duration',
      'portfolioSize'     => 'field_portfolio_size',
      'ownsInventory'     => 'field_owns_inventory',
      'operationsContact' => 'field_operations_contact',
      'financeContact'    => 'field_finance_contact',
    ];

    foreach ($field_map as $key => $field_name) {
      if (array_key_exists($key, $body) && $org->hasField($field_name)) {
        $org->set($field_name, $body[$key]);
      }
    }

    if (array_key_exists('website', $body) && $org->hasField('field_website')) {
      $org->set('field_website', $body['website'] ? ['uri' => $body['website'], 'title' => ''] : NULL);
    }

    if (array_key_exists('agencyServices', $body) && $org->hasField('field_agency_services') && is_array($body['agencyServices'])) {
      $org->set('field_agency_services', array_map(fn($s) => ['value' => $s], $body['agencyServices']));
    }

    $org->save();
    $is_active = $user->hasField('field_active_organization')
      && (int) $user->get('field_active_organization')->target_id === (int) $org->id();

    return new JsonResponse([
      'success' => TRUE,
      'message' => 'Organization updated.',
      'data'    => ['organization' => $this->serializeOrganization($org, $is_active)],
    ]);
  }

  // ── Serializers ──────────────────────────────────────────────────────────
  protected function serializeUser(User $user): array {
    return [
      'id'            => (int) $user->id(),
      'name'          => $user->getDisplayName(),
      'email'         => $user->getEmail(),
      'mobileNumber'  => $user->hasField('field_mobile_number') ? $user->get('field_mobile_number')->value : NULL,
      'designation'   => $user->hasField('field_designation')   ? $user->get('field_designation')->value   : NULL,
      'department'    => $user->hasField('field_department')     ? $user->get('field_department')->value     : NULL,
      'emailVerified' => $user->hasField('field_email_verified') ? (bool) $user->get('field_email_verified')->value : FALSE,
      'phoneVerified' => $user->hasField('field_phone_verified') ? (bool) $user->get('field_phone_verified')->value : FALSE,
      'trustScore'    => $user->hasField('field_trust_score')    ? (int) ($user->get('field_trust_score')->value ?? 50) : 50,
      'roles'         => array_values($user->getRoles(FALSE)),  // FALSE = exclude 'authenticated'
    ];
  }

  /**
   * Converts an Organisation node to the API shape the frontend expects.
   *
   * @param \Drupal\node\Entity\Node $org
   * @param bool $isActive  Whether this is the user's active organisation.
   */
  protected function serializeOrganization(Node $org, bool $isActive = FALSE): array {
    $org_type = $org->get('field_org_type')->value;

    // Resolve taxonomy term references to {id, name}.
    $division = $this->termRef($org->get('field_division')->first());
    $district = $this->termRef($org->get('field_district')->first());

    $data = [
      'id'                  => (int) $org->id(),
      'name'                => $org->getTitle(),
      'type'                => $org_type,
      'isActive'            => $isActive,
      'officialEmail'       => $org->hasField('field_official_email')    ? $org->get('field_official_email')->value  : NULL,
      'officialPhone'       => $org->hasField('field_official_phone')    ? $org->get('field_official_phone')->value  : NULL,
      'website'             => $org->hasField('field_website')           ? ($org->get('field_website')->uri ?? NULL)  : NULL,
      'division'            => $division,
      'district'            => $district,
      'fullAddress'         => $org->hasField('field_full_address')      ? $org->get('field_full_address')->value    : NULL,
      'businessRegNumber'   => $org->hasField('field_business_reg_number') ? $org->get('field_business_reg_number')->value : NULL,
      'tin'                 => $org->hasField('field_tin')               ? $org->get('field_tin')->value             : NULL,
      'establishmentYear'   => $org->hasField('field_establishment_year') ? ($org->get('field_establishment_year')->value ? (int) $org->get('field_establishment_year')->value : NULL) : NULL,
      'verificationStatus'  => $org->get('field_verification_status')->value ?? 'pending',
      'trustScore'          => (int) ($org->get('field_trust_score')->value ?? 50),
      'profileCompletion'   => (int) ($org->get('field_profile_completion')->value ?? 0),
    ];

    // Type-specific details.
    if ($org_type === 'brand') {
      $preferred_regions = $org->hasField('field_preferred_regions')
        ? $this->termRefs($org->get('field_preferred_regions'))
        : [];

      $data['brandDetails'] = [
        'parentCompany'       => $org->hasField('field_parent_company')      ? $org->get('field_parent_company')->value      : NULL,
        'annualBudgetRange'   => $org->hasField('field_annual_budget_range') ? $org->get('field_annual_budget_range')->value  : NULL,
        'bookingDuration'     => $org->hasField('field_booking_duration')    ? $org->get('field_booking_duration')->value     : NULL,
        'preferredRegions'    => $preferred_regions,
      ];
    }
    elseif ($org_type === 'agency') {
      $services         = $org->hasField('field_agency_services')    ? array_column($org->get('field_agency_services')->getValue(), 'value')    : [];
      $preferred_regions = $org->hasField('field_preferred_regions') ? $this->termRefs($org->get('field_preferred_regions')) : [];

      $data['agencyDetails'] = [
        'agencyServices'     => $services,
        'portfolioSize'      => $org->hasField('field_portfolio_size')       ? $org->get('field_portfolio_size')->value       : NULL,
        'ownsInventory'      => $org->hasField('field_owns_inventory')       ? (bool) $org->get('field_owns_inventory')->value : FALSE,
        'operationsContact'  => $org->hasField('field_operations_contact')   ? $org->get('field_operations_contact')->value   : NULL,
        'financeContact'     => $org->hasField('field_finance_contact')      ? $org->get('field_finance_contact')->value      : NULL,
        'preferredRegions'   => $preferred_regions,
      ];
    }
    elseif ($org_type === 'owner') {
      $data['ownerDetails'] = [
        'inventoryCount'          => (int) ($org->hasField('field_inventory_count')           ? ($org->get('field_inventory_count')->value ?? 0)           : 0),
        'coverageSqft'            => (float) ($org->hasField('field_total_coverage_sqft')     ? ($org->get('field_total_coverage_sqft')->value ?? 0)       : 0),
        'maintenanceCapability'   => $org->hasField('field_maintenance_capability')            ? $org->get('field_maintenance_capability')->value            : NULL,
        'installationServices'    => $org->hasField('field_installation_services')             ? (bool) $org->get('field_installation_services')->value      : FALSE,
      ];
    }

    return $data;
  }

}

