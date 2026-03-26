<?php

declare(strict_types=1);

namespace Drupal\billoria_core\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Notification Service.
 *
 * Handles sending notifications via email, in-app, etc.
 */
class NotificationService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * Constructs a NotificationService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    MailManagerInterface $mail_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get('billoria_core');
    $this->mailManager = $mail_manager;
  }

  /**
   * Notify about new billboard.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node.
   */
  public function notifyNewBillboard(NodeInterface $billboard): void {
    $this->logger->info('New billboard created: @title (@nid)', [
      '@title' => $billboard->getTitle(),
      '@nid' => $billboard->id(),
    ]);

    // TODO: Send notifications to subscribed agencies/brand users.
  }

  /**
   * Notify about billboard status change.
   *
   * @param \Drupal\node\NodeInterface $billboard
   *   The billboard node.
   * @param string $old_status
   *   Previous verification status.
   * @param string $new_status
   *   New verification status.
   */
  public function notifyBillboardStatusChange(NodeInterface $billboard, string $old_status, string $new_status): void {
    $this->logger->info('Billboard @nid status changed from @old to @new', [
      '@nid' => $billboard->id(),
      '@old' => $old_status,
      '@new' => $new_status,
    ]);

    // Notify owner.
    $owner_uid = $billboard->getOwnerId();
    if ($owner_uid) {
      $this->sendEmail(
        $owner_uid,
        'verification_update',
        [
          'title' => $billboard->getTitle(),
          'message' => sprintf(
            'Your billboard "%s" verification status has been changed to: %s',
            $billboard->getTitle(),
            $new_status
          ),
        ]
      );
    }
  }

  /**
   * Notify about new booking request.
   *
   * @param int $billboard_nid
   *   Billboard node ID.
   * @param int $requester_uid
   *   User ID of requester.
   * @param array $booking_data
   *   Booking information.
   */
  public function notifyNewBooking(int $billboard_nid, int $requester_uid, array $booking_data): void {
    $billboard = $this->entityTypeManager->getStorage('node')->load($billboard_nid);
    
    if (!$billboard) {
      return;
    }

    // Notify billboard owner.
    $owner_uid = $billboard->getOwnerId();
    if ($owner_uid) {
      $this->sendEmail(
        $owner_uid,
        'booking_notification',
        [
          'title' => $billboard->getTitle(),
          'message' => sprintf(
            'New booking request received for your billboard "%s" from %s to %s',
            $billboard->getTitle(),
            $booking_data['start_date'] ?? 'N/A',
            $booking_data['end_date'] ?? 'N/A'
          ),
        ]
      );
    }
  }

  /**
   * Send email notification.
   *
   * @param int $uid
   *   User ID.
   * @param string $key
   *   Mail key.
   * @param array $params
   *   Mail parameters.
   *
   * @return bool
   *   TRUE if sent successfully.
   */
  protected function sendEmail(int $uid, string $key, array $params): bool {
    $user_storage = $this->entityTypeManager->getStorage('user');
    $user = $user_storage->load($uid);

    if (!$user || !$user->getEmail()) {
      return FALSE;
    }

    $result = $this->mailManager->mail(
      'billoria_core',
      $key,
      $user->getEmail(),
      $user->getPreferredLangcode(),
      $params,
      NULL,
      TRUE
    );

    return $result['result'] ?? FALSE;
  }

}
