<?php

declare(strict_types=1);

namespace Drupal\billoria_core\EventSubscriber;

use Drupal\billoria_core\Service\NotificationService;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Billoria Core Event Subscriber.
 *
 * Listens to various system events.
 */
class BilloriaCoreSubscriber implements EventSubscriberInterface {

  /**
   * The notification service.
   *
   * @var \Drupal\billoria_core\Service\NotificationService
   */
  protected NotificationService $notificationService;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a BilloriaCoreSubscriber object.
   *
   * @param \Drupal\billoria_core\Service\NotificationService $notification_service
   *   The notification service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    NotificationService $notification_service,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->notificationService = $notification_service;
    $this->logger = $logger_factory->get('billoria_core');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::REQUEST][] = ['onRequest', 0];
    return $events;
  }

  /**
   * Subscriber callback for KernelEvents::REQUEST.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event.
   */
  public function onRequest(RequestEvent $event): void {
    // Add custom request handling logic here if needed.
    // For example: API authentication, rate limiting, etc.
  }

}
