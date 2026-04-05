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
    // Only title is required to start a billboard (draft/unpublished).
    if (empty($data['title'])) {
      throw new \Exception('Required field title is missing');
    }

    $storage = $this->entityTypeManager->getStorage('node');

    // Create as unpublished — publish requires all fields.
    $values = [
      'type' => 'billboard',
      'title' => $data['title'],
      'status' => 0,
      'uid' => $this->currentUser->id(),
      'field_review_status' => 'draft',
    ];

    // Map data to fields (exclude field_billboard_id — auto-generated).
    $field_mappings = [
      'field_media_format', 'field_placement_type',
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
      // Accept both "field_xxx" and "xxx" keys from frontend.
      $short_key = str_replace('field_', '', $field);
      if (isset($data[$field])) {
        $values[$field] = $data[$field];
      }
      elseif (isset($data[$short_key])) {
        $values[$field] = $data[$short_key];
      }
    }

    // Auto-set owner_organization from user profile if not provided.
    if (empty($values['field_owner_organization'])) {
      $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
      if ($user && $user->hasField('field_organization') && !$user->get('field_organization')->isEmpty()) {
        $values['field_owner_organization'] = $user->get('field_organization')->first()->target_id;
      }
    }

    // Create the billboard (unpublished draft — no billboard ID yet).
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
      'field_rate_card_price', 'field_currency', 'field_owner_organization',
      'field_owner_contact_number', 'field_owner_vendor_name',
      'field_is_premium', 'field_is_active', 'field_notes', 'field_review_status',
    ];

    foreach ($allowed_fields as $field) {
      // Accept both "field_xxx" and "xxx" keys from frontend.
      $short_key = str_replace('field_', '', $field);
      $value = $data[$field] ?? $data[$short_key] ?? NULL;
      if ($value !== NULL && $billboard->hasField($field)) {
        $billboard->set($field, $value);
      }
    }

    $billboard->save();

    $this->logger->info('Billboard @nid updated by user @uid', [
      '@nid' => $billboard->id(),
      '@uid' => $this->currentUser->id(),
    ]);

    return $billboard;
  }

  /**
   * Publish a billboard after validating all required fields.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node to publish.
   *
   * @return array
   *   Array with 'missing' fields (empty if ready) and 'billboard' node.
   *
   * @throws \Exception
   *   If node is not a billboard.
   */
  public function publishBillboard(NodeInterface $billboard): array {
    if ($billboard->bundle() !== 'billboard') {
      throw new \Exception('Not a billboard node');
    }

    // Fields required for submitting for review.
    $publish_required = [
      'field_owner_organization' => 'Owner Organization',
      'field_media_format' => 'Media Format',
      'field_latitude' => 'Latitude',
      'field_longitude' => 'Longitude',
      'field_division' => 'Division',
      'field_district' => 'District',
    ];

    $missing = [];
    foreach ($publish_required as $field => $label) {
      if (!$billboard->hasField($field) || $billboard->get($field)->isEmpty()) {
        $missing[] = $label;
      }
    }

    if (!empty($missing)) {
      return ['missing' => $missing, 'billboard' => $billboard];
    }

    // Generate billboard ID if not already set.
    if ($billboard->get('field_billboard_id')->isEmpty()) {
      $data = [];
      $ref_fields = [
        'field_owner_organization', 'field_city_corporation',
        'field_district', 'field_upazila_thana', 'field_media_format',
      ];
      foreach ($ref_fields as $ref) {
        if ($billboard->hasField($ref) && !$billboard->get($ref)->isEmpty()) {
          $data[$ref] = $billboard->get($ref)->target_id;
        }
      }
      $billboard->set('field_billboard_id', $this->generateBillboardId($data));
    }

    // Set review status to pending_review (stays unpublished until admin approves).
    $billboard->set('field_review_status', 'pending_review');
    $billboard->save();

    $this->logger->info('Billboard @nid submitted for review by user @uid', [
      '@nid' => $billboard->id(),
      '@uid' => $this->currentUser->id(),
    ]);

    return ['missing' => [], 'billboard' => $billboard];
  }

  /**
   * Generate a billboard ID: [AGENCY]-[CITY]-[THANA]-[TYPE]-[SEQ].
   *
   * @param array $data
   *   The billboard creation data.
   *
   * @return string
   *   The generated billboard ID.
   */
  protected function generateBillboardId(array $data): string {
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $node_storage = $this->entityTypeManager->getStorage('node');

    // [AGENCY] — abbreviation from organization name.
    $agency = 'ORG';
    if (!empty($data['field_owner_organization'])) {
      $org = $node_storage->load($data['field_owner_organization']);
      if ($org) {
        $agency = $this->abbreviate($org->label(), 3);
      }
    }

    // [CITY] — abbreviation from city corporation term.
    $city = 'CTY';
    if (!empty($data['field_city_corporation'])) {
      $term = $term_storage->load($data['field_city_corporation']);
      if ($term) {
        $city = $this->abbreviate($term->getName(), 3);
      }
    }
    elseif (!empty($data['field_district'])) {
      // Fallback to district if no city corporation selected.
      $term = $term_storage->load($data['field_district']);
      if ($term) {
        $city = $this->abbreviate($term->getName(), 3);
      }
    }

    // [THANA] — abbreviation from upazila/thana term.
    $thana = 'THN';
    if (!empty($data['field_upazila_thana'])) {
      $term = $term_storage->load($data['field_upazila_thana']);
      if ($term) {
        $thana = $this->abbreviate($term->getName(), 3);
      }
    }

    // [TYPE] — abbreviation from media format term.
    $type = 'BB';
    if (!empty($data['field_media_format'])) {
      $term = $term_storage->load($data['field_media_format']);
      if ($term) {
        $type = $this->abbreviate($term->getName(), 3);
      }
    }

    // [SEQ] — next sequence for this prefix combination.
    $prefix = implode('-', [$agency, $city, $thana, $type]);
    $seq = $this->getNextSequence($prefix);

    return $prefix . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
  }

  /**
   * Generate abbreviation from a name.
   *
   * Multi-word: first letter of each word (e.g., "Dhaka North" → "DN").
   * Single-word: first N characters (e.g., "Gulshan" → "GUL").
   *
   * @param string $name
   *   The full name.
   * @param int $maxLen
   *   Max characters for single-word abbreviation.
   *
   * @return string
   *   Uppercase abbreviation.
   */
  protected function abbreviate(string $name, int $maxLen = 3): string {
    $words = preg_split('/[\s\-_]+/', trim($name));
    $words = array_filter($words, fn($w) => strlen($w) > 0);
    $words = array_values($words);

    if (count($words) > 1) {
      // Multi-word: first letter of each word.
      $abbr = '';
      foreach ($words as $w) {
        $abbr .= mb_strtoupper(mb_substr($w, 0, 1));
      }
      return $abbr;
    }

    // Single word: first N characters.
    return mb_strtoupper(mb_substr($words[0] ?? 'UNK', 0, $maxLen));
  }

  /**
   * Get the next sequence number for a billboard ID prefix.
   *
   * Queries existing billboards with the same prefix pattern.
   *
   * @param string $prefix
   *   The billboard ID prefix (e.g., "XYA-DNCC-GUL-SB").
   *
   * @return int
   *   The next sequence number.
   */
  protected function getNextSequence(string $prefix): int {
    $node_storage = $this->entityTypeManager->getStorage('node');

    $query = $node_storage->getQuery()
      ->condition('type', 'billboard')
      ->condition('field_billboard_id', $prefix . '-', 'STARTS_WITH')
      ->accessCheck(FALSE)
      ->sort('created', 'DESC')
      ->range(0, 1);

    $nids = $query->execute();
    if (empty($nids)) {
      return 1;
    }

    $billboard = $node_storage->load(reset($nids));
    if (!$billboard || !$billboard->hasField('field_billboard_id')) {
      return 1;
    }

    $existing_id = $billboard->get('field_billboard_id')->value ?? '';
    // Extract the numeric suffix after the last dash.
    if (preg_match('/-(\d+)$/', $existing_id, $matches)) {
      return (int) $matches[1] + 1;
    }

    return 1;
  }

}
