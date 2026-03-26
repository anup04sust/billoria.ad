<?php

namespace Drupal\billoria_accounts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * REST API controller for user profile and dashboard data.
 *
 * Provides JSON API for Next.js frontend.
 */
class ApiProfileController extends ControllerBase {

  /**
   * Get current user profile with organization data.
   *
   * GET /api/user/profile
   *
   * Returns:
   * {
   *   "user": {...},
   *   "organization": {...},
   *   "verificationStatus": {...},
   *   "profileCompletion": 75
   * }
   */
  public function getProfile() {
    $user = User::load($this->currentUser()->id());

    if (!$user) {
      return new JsonResponse(['error' => 'User not found'], 404);
    }

    // Get active organization
    $org_ref = $user->get('field_active_organization')->entity;

    if (!$org_ref) {
      return new JsonResponse([
        'user' => $this->serializeUser($user),
        'organization' => NULL,
        'message' => 'No organization profile found',
      ]);
    }

    $organization = $org_ref;

    return new JsonResponse([
      'user' => $this->serializeUser($user),
      'organization' => $this->serializeOrganization($organization),
      'verificationStatus' => [
        'emailVerified' => (bool) $user->get('field_email_verified')->value,
        'phoneVerified' => (bool) $user->get('field_phone_verified')->value,
        'businessVerified' => $organization->get('field_verification_status')->value === 'verified',
        'trustScore' => (int) ($organization->get('field_trust_score')->value ?? 50),
      ],
      'profileCompletion' => (int) ($organization->get('field_profile_completion')->value ?? 0),
    ]);
  }

  /**
   * Get organization status and stats.
   *
   * GET /api/organization/{nid}/status
   */
  public function getOrganizationStatus($nid) {
    $organization = Node::load($nid);

    if (!$organization || $organization->bundle() !== 'organization') {
      return new JsonResponse(['error' => 'Organization not found'], 404);
    }

    // Check if current user has access to this org
    $user = User::load($this->currentUser()->id());
    $user_orgs = array_column($user->get('field_organization')->getValue(), 'target_id');

    if (!in_array($nid, $user_orgs)) {
      return new JsonResponse(['error' => 'Access denied'], 403);
    }

    $org_type = $organization->get('field_org_type')->value;
    $verification_status = $organization->get('field_verification_status')->value;
    $trust_score = $organization->get('field_trust_score')->value ?? 50;
    $profile_completion = $organization->get('field_profile_completion')->value ?? 0;

    $response = [
      'id' => (int) $organization->id(),
      'name' => $organization->getTitle(),
      'type' => $org_type,
      'verificationStatus' => $verification_status,
      'trustScore' => (int) $trust_score,
      'profileCompletion' => (int) $profile_completion,
      'stats' => [],
    ];

    // Add type-specific stats
    if ($org_type === 'owner') {
      $response['stats']['inventoryCount'] = (int) ($organization->get('field_inventory_count')->value ?? 0);
      $response['stats']['coverageSqft'] = (float) ($organization->get('field_total_coverage_sqft')->value ?? 0);
    }
    elseif ($org_type === 'agency') {
      $response['stats']['portfolioSize'] = $organization->get('field_portfolio_size')->value;
      $response['stats']['ownsInventory'] = (bool) $organization->get('field_owns_inventory')->value;
    }

    return new JsonResponse($response);
  }

  /**
   * Serialize user entity for API response.
   */
  protected function serializeUser(User $user) {
    return [
      'id' => (int) $user->id(),
      'name' => $user->getDisplayName(),
      'email' => $user->getEmail(),
      'mobileNumber' => $user->get('field_mobile_number')->value,
      'designation' => $user->get('field_designation')->value,
      'emailVerified' => (bool) $user->get('field_email_verified')->value,
      'phoneVerified' => (bool) $user->get('field_phone_verified')->value,
      'isPrimaryAdmin' => (bool) $user->get('field_is_primary_admin')->value,
    ];
  }

  /**
   * Serialize organization node for API response.
   */
  protected function serializeOrganization(Node $organization) {
    $org_type = $organization->get('field_org_type')->value;

    $data = [
      'id' => (int) $organization->id(),
      'name' => $organization->getTitle(),
      'type' => $org_type,
      'officialEmail' => $organization->get('field_official_email')->value,
      'officialPhone' => $organization->get('field_official_phone')->value,
      'website' => $organization->get('field_website')->uri ?? NULL,
      'division' => $organization->get('field_division')->target_id,
      'district' => $organization->get('field_district')->target_id,
      'fullAddress' => $organization->get('field_full_address')->value,
      'verificationStatus' => $organization->get('field_verification_status')->value,
      'trustScore' => (int) ($organization->get('field_trust_score')->value ?? 50),
      'profileCompletion' => (int) ($organization->get('field_profile_completion')->value ?? 0),
    ];

    // Add type-specific fields
    if ($org_type === 'brand') {
      $data['brandDetails'] = [
        'parentCompany' => $organization->get('field_parent_company')->value,
        'annualBudgetRange' => $organization->get('field_annual_budget_range')->value,
        'bookingDuration' => $organization->get('field_booking_duration')->value,
        'preferredRegions' => array_column($organization->get('field_preferred_regions')->getValue(), 'target_id'),
      ];
    }
    elseif ($org_type === 'agency') {
      $data['agencyDetails'] = [
        'services' => array_column($organization->get('field_agency_services')->getValue(), 'value'),
        'portfolioSize' => $organization->get('field_portfolio_size')->value,
        'ownsInventory' => (bool) $organization->get('field_owns_inventory')->value,
        'operationsContact' => $organization->get('field_operations_contact')->value,
        'financeContact' => $organization->get('field_finance_contact')->value,
      ];
    }
    elseif ($org_type === 'owner') {
      $data['ownerDetails'] = [
        'inventoryCount' => (int) ($organization->get('field_inventory_count')->value ?? 0),
        'coverageSqft' => (float) ($organization->get('field_total_coverage_sqft')->value ?? 0),
        'maintenanceCapability' => $organization->get('field_maintenance_capability')->value,
        'installationServices' => (bool) $organization->get('field_installation_services')->value,
        'coverageDistricts' => array_column($organization->get('field_coverage_districts')->getValue(), 'target_id'),
      ];
    }

    return $data;
  }

}
