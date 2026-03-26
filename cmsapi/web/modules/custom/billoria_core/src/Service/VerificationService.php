<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Verification Service.
 *
 * Handles verification workflows for billboards, agencies, vendors.
 */
class VerificationService {

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
   * Constructs a VerificationService object.
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
   * Verify an entity (billboard, agency, vendor).
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to verify.
   * @param string $status
   *   Verification status (verified, rejected, pending).
   * @param string $notes
   *   Verification notes.
   *
   * @return bool
   *   TRUE if successful.
   */
  public function verify(NodeInterface $entity, string $status, string $notes = ''): bool {
    // Check permission.
    if (!$this->canVerify($entity)) {
      $this->logger->warning('User @uid attempted verification without permission', [
        '@uid' => $this->currentUser->id(),
      ]);
      return FALSE;
    }

    // Update verification field.
    if ($entity->hasField('field_verification_status')) {
      $entity->set('field_verification_status', $status);
      
      // Add verification notes with timestamp.
      if ($entity->hasField('field_legal_notes') && !empty($notes)) {
        $timestamp = date('Y-m-d H:i:s');
        $verifier = $this->currentUser->getDisplayName();
        $note = sprintf("[%s] Verified by %s: %s", $timestamp, $verifier, $notes);
        
        $existing = $entity->get('field_legal_notes')->value ?? '';
        $entity->set('field_legal_notes', $existing . "\n\n" . $note);
      }

      $entity->save();

      $this->logger->info('Entity @type @id verified with status @status', [
        '@type' => $entity->bundle(),
        '@id' => $entity->id(),
        '@status' => $status,
      ]);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if current user can verify entities.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE if user has permission.
   */
  protected function canVerify(NodeInterface $entity): bool {
    // Platform admins can verify everything.
    if ($this->currentUser->hasPermission('administer billoria')) {
      return TRUE;
    }

    // Specific permissions by entity type.
    $bundle = $entity->bundle();
    return $this->currentUser->hasPermission("verify {$bundle}");
  }

  /**
   * Get pending verifications count.
   *
   * @param string $bundle
   *   Content type bundle.
   *
   * @return int
   *   Count of pending verifications.
   */
  public function getPendingCount(string $bundle): int {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->condition('type', $bundle)
      ->condition('field_verification_status', 'pending')
      ->accessCheck(FALSE);

    return (int) $query->count()->execute();
  }

}
