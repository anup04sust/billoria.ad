<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Booking Manager Service.
 *
 * Handles booking request business logic.
 */
class BookingManager {

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
   * The billboard manager.
   *
   * @var \Drupal\billoria_core\Service\BillboardManager
   */
  protected BillboardManager $billboardManager;

  /**
   * Constructs a BookingManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\billoria_core\Service\BillboardManager $billboard_manager
   *   The billboard manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountProxyInterface $current_user,
    LoggerChannelFactoryInterface $logger_factory,
    BillboardManager $billboard_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->logger = $logger_factory->get('billoria_core');
    $this->billboardManager = $billboard_manager;
  }

  /**
   * Create a booking request.
   *
   * @param int $billboard_nid
   *   Billboard node ID.
   * @param array $booking_data
   *   Booking information (start_date, end_date, budget, notes, etc.).
   *
   * @return array
   *   Result with success status and booking ID or error message.
   */
  public function createBookingRequest(int $billboard_nid, array $booking_data): array {
    try {
      // Load billboard.
      $billboard = $this->entityTypeManager->getStorage('node')->load($billboard_nid);
      
      if (!$billboard || $billboard->bundle() !== 'billboard') {
        return [
          'success' => FALSE,
          'error' => 'Invalid billboard',
        ];
      }

      // Validate availability.
      $start_date = $booking_data['start_date'] ?? '';
      $end_date = $booking_data['end_date'] ?? '';
      
      if (!$this->billboardManager->isAvailableForBooking($billboard, $start_date, $end_date)) {
        return [
          'success' => FALSE,
          'error' => 'Billboard not available for selected dates',
        ];
      }

      // Calculate pricing.
      $pricing = $this->billboardManager->calculateBillboardPrice($billboard, $start_date, $end_date);

      // TODO: Create booking custom entity when implemented.
      // For now, log the request.
      $this->logger->info('Booking request created for billboard @nid by user @uid', [
        '@nid' => $billboard_nid,
        '@uid' => $this->currentUser->id(),
      ]);

      return [
        'success' => TRUE,
        'booking_id' => NULL, // Will be actual ID when custom entity is created
        'pricing' => $pricing,
        'message' => 'Booking request created successfully',
      ];

    }
    catch (\Exception $e) {
      $this->logger->error('Error creating booking request: @message', [
        '@message' => $e->getMessage(),
      ]);
      
      return [
        'success' => FALSE,
        'error' => 'Failed to create booking request',
      ];
    }
  }

  /**
   * Get bookings by user.
   *
   * @param int $uid
   *   User ID.
   * @param string|null $status
   *   Optional status filter.
   *
   * @return array
   *   Array of booking entities.
   */
  public function getBookingsByUser(int $uid, ?string $status = NULL): array {
    // TODO: Query booking custom entities when implemented.
    $this->logger->info('Fetching bookings for user @uid', ['@uid' => $uid]);
    return [];
  }

  /**
   * Update booking status.
   *
   * @param int $booking_id
   *   Booking entity ID.
   * @param string $new_status
   *   New status value.
   * @param string $notes
   *   Optional notes about the status change.
   *
   * @return bool
   *   TRUE if successful, FALSE otherwise.
   */
  public function updateBookingStatus(int $booking_id, string $new_status, string $notes = ''): bool {
    // TODO: Update booking custom entity when implemented.
    $this->logger->info('Booking @id status changed to @status', [
      '@id' => $booking_id,
      '@status' => $new_status,
    ]);
    
    return TRUE;
  }

  /**
   * Validate booking dates.
   *
   * @param string $start_date
   *   Start date in YYYY-MM-DD format.
   * @param string $end_date
   *   End date in YYYY-MM-DD format.
   *
   * @return array
   *   Validation result with 'valid' boolean and 'errors' array.
   */
  public function validateBookingDates(string $start_date, string $end_date): array {
    $errors = [];

    // Parse dates.
    $start = \DateTime::createFromFormat('Y-m-d', $start_date);
    $end = \DateTime::createFromFormat('Y-m-d', $end_date);
    $now = new \DateTime();

    if (!$start) {
      $errors[] = 'Invalid start date format';
    }

    if (!$end) {
      $errors[] = 'Invalid end date format';
    }

    if ($start && $start < $now) {
      $errors[] = 'Start date cannot be in the past';
    }

    if ($start && $end && $start >= $end) {
      $errors[] = 'End date must be after start date';
    }

    // Check minimum booking duration (if configured).
    if ($start && $end) {
      $duration = $start->diff($end)->days;
      $min_duration = 7; // Default 7 days, should be configurable.
      
      if ($duration < $min_duration) {
        $errors[] = sprintf('Minimum booking duration is %d days', $min_duration);
      }
    }

    return [
      'valid' => empty($errors),
      'errors' => $errors,
    ];
  }

}
