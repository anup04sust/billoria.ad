<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Agency Manager Service.
 *
 * Handles agency-related business logic.
 */
class AgencyManager {

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
   * Constructs an AgencyManager object.
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
   * Get agency profile by user ID.
   *
   * @param int $uid
   *   User ID.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Agency profile node or NULL.
   */
  public function getAgencyProfileByUser(int $uid): ?NodeInterface {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'agency_profile')
      ->condition('uid', $uid)
      ->accessCheck(TRUE)
      ->range(0, 1);

    $nids = $query->execute();
    
    if ($nids) {
      $nid = reset($nids);
      return $storage->load($nid);
    }

    return NULL;
  }

  /**
   * Get all verified agencies.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of agency profile nodes.
   */
  public function getVerifiedAgencies(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'agency_profile')
      ->condition('status', 1)
      ->condition('field_verification_status', 'verified')
      ->accessCheck(TRUE);

    $nids = $query->execute();
    return $nids ? $storage->loadMultiple($nids) : [];
  }

  /**
   * Search agencies by criteria.
   *
   * @param array $criteria
   *   Search criteria (service_areas, verification_status, etc.).
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of agency profile nodes.
   */
  public function searchAgencies(array $criteria = []): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', 'agency_profile')
      ->condition('status', 1)
      ->accessCheck(TRUE);

    // Filter by service areas.
    if (!empty($criteria['service_areas'])) {
      $query->condition('field_service_areas', $criteria['service_areas'], 'IN');
    }

    // Filter by verification status.
    if (!empty($criteria['verification_status'])) {
      $query->condition('field_verification_status', $criteria['verification_status']);
    }

    $nids = $query->execute();
    return $nids ? $storage->loadMultiple($nids) : [];
  }

  /**
   * Get agency statistics.
   *
   * @param int $agency_nid
   *   Agency profile node ID.
   *
   * @return array
   *   Statistics array.
   */
  public function getAgencyStatistics(int $agency_nid): array {
    // TODO: Calculate agency statistics.
    // - Total billboards managed
    // - Total bookings
    // - Revenue generated
    // - Average rating
    
    return [
      'total_billboards' => 0,
      'total_bookings' => 0,
      'revenue' => 0,
      'rating' => 0,
    ];
  }

}
