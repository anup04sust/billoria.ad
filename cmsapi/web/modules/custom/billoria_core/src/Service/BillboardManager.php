<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Billboard Manager Service.
 *
 * Handles business logic for billboard operations.
 */
class BillboardManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a BillboardManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->logger = $logger_factory->get('billoria_core');
  }

  /**
   * Get available billboards by criteria.
   *
   * @param array $criteria
   *   Search criteria (district, billboard_type, availability, etc.).
   * @param int $limit
   *   Number of results to return.
   * @param int $offset
   *   Offset for pagination.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of billboard nodes.
   */
  public function getAvailableBillboards(array $criteria = [], int $limit = 20, int $offset = 0): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'billboard')
      ->condition('status', 1)
      ->accessCheck(TRUE)
      ->range($offset, $limit);

    // Filter by district.
    if (!empty($criteria['district'])) {
      $query->condition('field_district', $criteria['district']);
    }

    // Filter by billboard type.
    if (!empty($criteria['billboard_type'])) {
      $query->condition('field_billboard_type', $criteria['billboard_type']);
    }

    // Filter by availability status.
    if (!empty($criteria['availability_status'])) {
      $query->condition('field_availability_status', $criteria['availability_status']);
    }

    // Filter by verification status.
    if (isset($criteria['verified']) && $criteria['verified']) {
      $query->condition('field_verification_status', 'verified');
    }

    // Filter by price range.
    if (!empty($criteria['min_price'])) {
      $query->condition('field_base_price', $criteria['min_price'], '>=');
    }
    if (!empty($criteria['max_price'])) {
      $query->condition('field_base_price', $criteria['max_price'], '<=');
    }

    $nids = $query->execute();

    return $nids ? $storage->loadMultiple($nids) : [];
  }

  /**
   * Check if billboard is available for booking.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node.
   * @param string $start_date
   *   Start date in YYYY-MM-DD format.
   * @param string $end_date
   *   End date in YYYY-MM-DD format.
   *
   * @return bool
   *   TRUE if available, FALSE otherwise.
   */
  public function isAvailableForBooking(NodeInterface $billboard, string $start_date, string $end_date): bool {
    if ($billboard->bundle() !== 'billboard') {
      return FALSE;
    }

    // Check availability status field.
    if ($billboard->hasField('field_availability_status')) {
      $status = $billboard->get('field_availability_status')->value;
      if ($status !== 'available') {
        return FALSE;
      }
    }

    // Check if billboard is verified.
    if ($billboard->hasField('field_verification_status')) {
      $verification = $billboard->get('field_verification_status')->value;
      if ($verification !== 'verified') {
        return FALSE;
      }
    }

    // TODO: Check for conflicting bookings in date range.
    // This will be implemented when booking custom entity is created.

    return TRUE;
  }

  /**
   * Calculate billboard pricing.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node.
   * @param string $start_date
   *   Start date.
   * @param string $end_date
   *   End date.
   *
   * @return array
   *   Pricing breakdown with base_price, duration, total, etc.
   */
  public function calculateBillboardPrice(NodeInterface $billboard, string $start_date, string $end_date): array {
    $pricing_calculator = \Drupal::service('billoria_core.pricing_calculator');
    return $pricing_calculator->calculate($billboard, $start_date, $end_date);
  }

  /**
   * Verify billboard information.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node.
   * @param string $status
   *   Verification status (verified, rejected, pending).
   * @param string $notes
   *   Verification notes.
   *
   * @return bool
   *   TRUE if successful, FALSE otherwise.
   */
  public function verifyBillboard(NodeInterface $billboard, string $status, string $notes = ''): bool {
    if ($billboard->bundle() !== 'billboard') {
      return FALSE;
    }

    // Check permission.
    if (!$this->currentUser->hasPermission('verify billboards')) {
      $this->logger->error('User @uid attempted to verify billboard without permission.', [
        '@uid' => $this->currentUser->id(),
      ]);
      return FALSE;
    }

    // Update verification status.
    if ($billboard->hasField('field_verification_status')) {
      $billboard->set('field_verification_status', $status);

      // Add notes if field exists.
      if ($billboard->hasField('field_legal_notes') && !empty($notes)) {
        $existing_notes = $billboard->get('field_legal_notes')->value ?? '';
        $updated_notes = $existing_notes . "\n\n[" . date('Y-m-d H:i') . "] Verification: " . $notes;
        $billboard->set('field_legal_notes', $updated_notes);
      }

      $billboard->save();

      $this->logger->info('Billboard @nid verification status changed to @status by @uid', [
        '@nid' => $billboard->id(),
        '@status' => $status,
        '@uid' => $this->currentUser->id(),
      ]);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Get billboards by owner.
   *
   * @param int $owner_uid
   *   User ID of the owner.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of billboard nodes.
   */
  public function getBillboardsByOwner(int $owner_uid): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'billboard')
      ->condition('field_owner', $owner_uid)
      ->accessCheck(TRUE);

    $nids = $query->execute();
    return $nids ? $storage->loadMultiple($nids) : [];
  }

  /**
   * Get billboards by agency.
   *
   * @param int $agency_nid
   *   Node ID of the agency profile.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of billboard nodes.
   */
  public function getBillboardsByAgency(int $agency_nid): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'billboard')
      ->condition('field_agency', $agency_nid)
      ->accessCheck(TRUE);

    $nids = $query->execute();
    return $nids ? $storage->loadMultiple($nids) : [];
  }

  /**
   * Update billboard availability.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node.
   * @param string $status
   *   New availability status.
   *
   * @return bool
   *   TRUE if updated successfully.
   */
  public function updateAvailability(NodeInterface $billboard, string $status): bool {
    if ($billboard->bundle() !== 'billboard') {
      return FALSE;
    }

    if ($billboard->hasField('field_availability_status')) {
      $billboard->set('field_availability_status', $status);
      $billboard->save();
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Create a new billboard.
   *
   * @param array $data
   *   Billboard data array.
   *
   * @return \Drupal\node\NodeInterface
   *   The created billboard node.
   *
   * @throws \Exception
   *   If required fields are missing or invalid.
   */
  public function createBillboard(array $data): NodeInterface {
    // Validate required fields.
    $required = ['title', 'field_owner_organization', 'field_media_format', 'field_latitude', 'field_longitude'];
    foreach ($required as $field) {
      if (empty($data[$field])) {
        throw new \Exception("Required field $field is missing");
      }
    }

    $storage = $this->entityTypeManager->getStorage('node');

    // Prepare node values.
    $values = [
      'type' => 'billboard',
      'title' => $data['title'],
      'status' => 1,
      'uid' => $this->currentUser->id(),
    ];

    // Map data to fields.
    $field_mappings = [
      'field_billboard_id', 'field_media_format', 'field_placement_type',
      'field_road_name', 'field_road_type', 'field_division', 'field_district',
      'field_upazila_thana', 'field_city_corporation', 'field_area_zone',
      'field_traffic_direction', 'field_visibility_class', 'field_illumination_type',
      'field_booking_mode', 'field_availability_status', 'field_latitude',
      'field_longitude', 'field_facing_direction', 'field_visibility_distance',
      'field_width_ft', 'field_height_ft', 'field_display_size', 'field_lane_count',
      'field_has_divider', 'field_commercial_score', 'field_traffic_score',
      'field_rate_card_price', 'field_currency', 'field_owner_organization',
      'field_owner_vendor_name', 'field_owner_contact_number', 'field_is_premium',
      'field_is_active', 'field_notes',
    ];

    foreach ($field_mappings as $field) {
      if (isset($data[$field])) {
        $values[$field] = $data[$field];
      }
    }

    // Create the billboard.
    $billboard = $storage->create($values);
    $billboard->save();

    $this->logger->info('Billboard @nid created by user @uid', [
      '@nid' => $billboard->id(),
      '@uid' => $this->currentUser->id(),
    ]);

    return $billboard;
  }

  /**
   * Update an existing billboard.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node to update.
   * @param array $data
   *   Updated data array.
   *
   * @return \Drupal\node\NodeInterface
   *   The updated billboard node.
   *
   * @throws \Exception
   *   If validation fails.
   */
  public function updateBillboard(NodeInterface $billboard, array $data): NodeInterface {
    if ($billboard->bundle() !== 'billboard') {
      throw new \Exception('Not a billboard node');
    }

    // Update allowed fields.
    $allowed_fields = [
      'title', 'field_billboard_id', 'field_media_format', 'field_placement_type',
      'field_road_name', 'field_road_type', 'field_division', 'field_district',
      'field_upazila_thana', 'field_city_corporation', 'field_area_zone',
      'field_traffic_direction', 'field_visibility_class', 'field_illumination_type',
      'field_booking_mode', 'field_availability_status', 'field_latitude',
      'field_longitude', 'field_facing_direction', 'field_visibility_distance',
      'field_width_ft', 'field_height_ft', 'field_display_size', 'field_lane_count',
      'field_has_divider', 'field_commercial_score', 'field_traffic_score',
      'field_rate_card_price', 'field_currency', 'field_owner_contact_number',
      'field_is_premium', 'field_is_active', 'field_notes',
    ];

    foreach ($allowed_fields as $field) {
      if (isset($data[$field])) {
        if ($billboard->hasField($field)) {
          $billboard->set($field, $data[$field]);
        }
      }
    }

    $billboard->save();

    $this->logger->info('Billboard @nid updated by user @uid', [
      '@nid' => $billboard->id(),
      '@uid' => $this->currentUser->id(),
    ]);

    return $billboard;
  }

}
