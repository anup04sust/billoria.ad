<?php

namespace Drupal\billoria_core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;

/**
 * Restricts CMS UI access to platform administrators only.
 *
 * Problem: API-authenticated users share session cookies with CMS,
 * allowing them to access Drupal admin pages (e.g., /user, /node/add).
 *
 * Solution: Block non-admin users from accessing CMS routes.
 * Only users with 'platform_admin' role can access the CMS UI.
 * All other authenticated users are API-only.
 */
class ApiOnlyAccessSubscriber implements EventSubscriberInterface
{

    /**
     * The current user.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * CMS routes that should be restricted to platform admins only.
     *
     * @var array
     */
    protected $restrictedPaths = [
        '/admin',
        '/user',
        '/node/add',
        '/node/*/edit',
        '/node/*/delete',
        '/taxonomy',
        '/batch',
    ];

    /**
     * Constructs the event subscriber.
     *
     * @param \Drupal\Core\Session\AccountProxyInterface $current_user
     *   The current user.
     */
    public function __construct(AccountProxyInterface $current_user)
    {
        $this->currentUser = $current_user;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // Run early to block access before other systems
        $events[KernelEvents::REQUEST][] = ['onRequest', 100];
        return $events;
    }

    /**
     * Blocks non-admin users from accessing CMS UI routes.
     *
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     *   The request event.
     */
    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Skip API routes - they're allowed
        if (str_starts_with($path, '/api/')) {
            return;
        }

        // Skip certain routes that should always be accessible
        $allowed_routes = [
            '/user/login',
            '/user/logout',
            '/user/password',
            '/user/reset',
            '/',
            '/favicon.ico',
            '/robots.txt',
        ];

        foreach ($allowed_routes as $allowed) {
            if ($path === $allowed || str_starts_with($path, $allowed . '/')) {
                return;
            }
        }

        // Check if current path matches any restricted pattern
        $is_restricted = FALSE;
        foreach ($this->restrictedPaths as $restricted) {
            // Handle wildcard patterns
            $pattern = str_replace('*', '[^/]+', $restricted);
            if (preg_match('#^' . $pattern . '#', $path)) {
                $is_restricted = TRUE;
                break;
            }
        }

        if (!$is_restricted) {
            return;
        }

        // Allow access only to platform admins
        if ($this->currentUser->isAuthenticated()) {
            $roles = $this->currentUser->getRoles();
            $is_admin = in_array('platform_admin', $roles) ||
                in_array('administrator', $roles) ||
                $this->currentUser->id() == 1; // User 1 is always admin

            if (!$is_admin) {
                $response = new RedirectResponse(
                    Url::fromRoute('<front>')->toString(),
                    302
                );
                $response->headers->set('X-Drupal-Message', 'CMS access restricted to administrators only');
                $event->setResponse($response);
            }
        }
    }
}
