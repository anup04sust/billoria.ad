<?php

namespace Drupal\billoria_accounts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\user\UserAuthInterface;
use Drupal\Core\Security\RequestSanitizer;

/**
 * Custom login endpoint that handles already-authenticated sessions.
 *
 * POST /api/v1/auth/login
 *
 * Unlike Drupal's built-in /user/login which refuses authenticated users
 * with a 403, this endpoint:
 *  - Returns current session data when the user is already logged in.
 *  - Authenticates and creates a new session when the user is anonymous.
 */
class ApiAuthController extends ControllerBase {

  /**
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * Constructs a new ApiAuthController.
   */
  public function __construct(UserAuthInterface $user_auth) {
    $this->userAuth = $user_auth;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.auth')
    );
  }

  /**
   * Handles POST /api/v1/auth/login.
   *
   * If the requester already has an authenticated session, returns
   * their current user data and fresh tokens without re-authenticating.
   * Otherwise validates credentials and establishes a new session.
   *
   * Request body:
   * { "name": "user@example.com", "pass": "secret" }
   *
   * Success response (200):
   * {
   *   "current_user": { "uid": "1", "name": "...", "roles": [...] },
   *   "csrf_token": "...",
   *   "logout_token": "..."
   * }
   */
  public function login(Request $request): JsonResponse {
    $data = json_decode($request->getContent(), TRUE);

    $hasCredentials = !empty($data['name']) && !empty($data['pass']);
    $current_user   = $this->currentUser();

    // ── Already authenticated with NO credentials supplied ───────────────────
    // (e.g. a token-refresh call — return the current session as-is.)
    if (!$hasCredentials && $current_user->isAuthenticated()) {
      return $this->buildSessionResponse($current_user->id());
    }

    // ── Credentials supplied: always authenticate explicitly ─────────────────
    // This ensures a stale browser session for a different user is ignored.
    if (!$hasCredentials) {
      return new JsonResponse(['message' => 'Missing name or pass.'], 400);
    }

    $name = trim($data['name']);
    $pass = $data['pass'];

    // Try email first, fall back to username.
    $uid = NULL;
    $by_email = $this->entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['mail' => $name]);

    if (!empty($by_email)) {
      $candidate = reset($by_email);
      $uid = $this->userAuth->authenticate($candidate->getAccountName(), $pass);
    }

    if (!$uid) {
      $uid = $this->userAuth->authenticate($name, $pass);
    }

    if (!$uid) {
      return new JsonResponse([
        'message' => 'Sorry, unrecognized username or password.',
      ], 400);
    }

    $user = User::load($uid);
    if (!$user || !$user->isActive()) {
      return new JsonResponse([
        'message' => 'The user has not been activated or is blocked.',
      ], 403);
    }

    // If a different user is currently logged in, log them out first.
    if ($current_user->isAuthenticated() && (int) $current_user->id() !== (int) $uid) {
      user_logout();
    }

    // Establish Drupal session for the authenticated user.
    user_login_finalize($user);

    return $this->buildSessionResponse($uid);
  }

  /**
   * Builds the standard login JSON response for a given uid.
   */
  protected function buildSessionResponse(int|string $uid): JsonResponse {
    $user = User::load($uid);
    $csrf  = \Drupal::service('csrf_token');

    return new JsonResponse([
      'current_user' => [
        'uid'   => (string) $user->id(),
        'name'  => $user->getDisplayName(),
        'roles' => array_values($user->getRoles()),
      ],
      'csrf_token'   => $csrf->get('rest'),
      'logout_token' => $csrf->get('logout'),
    ]);
  }

}
