const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  'https://billoria-ad-api.ddev.site';

const FALLBACK_URLS = [
  'https://billoria-ad-api.ddev.site',
  'https://api.billoria-ad.ddev.site',
  'http://billoria-ad-api.ddev.site:33000',
];

async function apiFetch(path: string, options: RequestInit = {}): Promise<Response> {
  const urls = [...new Set([API_BASE_URL, ...FALLBACK_URLS])];
  let lastError: Error | null = null;

  for (const baseUrl of urls) {
    try {
      const response = await fetch(`${baseUrl}${path}`, {
        ...options,
        mode: 'cors',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          ...options.headers,
        },
      });
      if (response.ok || response.status === 400 || response.status === 403 || response.status === 422) {
        return response;
      }
    } catch (err) {
      lastError = err as Error;
    }
  }
  throw lastError || new Error('All API URLs failed');
}

// ─── Storage helpers (client-side only) ──────────────────────────────────────

export type AuthUser = {
  uid: string;
  name: string;
  roles: string[];
};

type AuthSession = {
  user: AuthUser;
  csrfToken: string;
  logoutToken: string;
};

const SESSION_KEY = 'billoria_auth';
const AUTH_COOKIE = 'billoria_logged_in';

/**
 * Builds secure cookie string with appropriate flags.
 * 
 * Security flags:
 * - Secure: HTTPS only (in production)
 * - SameSite=Strict: Prevents CSRF attacks
 * - Path=/: Available across entire site
 * 
 * Note: HttpOnly cannot be set via JavaScript - only server-side.
 * The Drupal backend session cookies are HttpOnly.
 */
function buildCookieString(name: string, value: string, maxAge: number): string {
  const isHttps = typeof window !== 'undefined' && window.location.protocol === 'https:';
  const secure = isHttps ? 'Secure; ' : '';

  return `${name}=${value}; path=/; max-age=${maxAge}; SameSite=Strict; ${secure}`.trim();
}

export function getSession(): AuthSession | null {
  if (typeof window === 'undefined') return null;
  try {
    const raw = localStorage.getItem(SESSION_KEY);
    return raw ? (JSON.parse(raw) as AuthSession) : null;
  } catch {
    return null;
  }
}

function saveSession(session: AuthSession): void {
  localStorage.setItem(SESSION_KEY, JSON.stringify(session));
  // Set a secure cookie so Next.js middleware can read auth state.
  // Max-age: 23 days (2000000 seconds) — matches Drupal session lifetime.
  document.cookie = buildCookieString(AUTH_COOKIE, '1', 2000000);
}

function clearSession(): void {
  localStorage.removeItem(SESSION_KEY);
  document.cookie = buildCookieString(AUTH_COOKIE, '', 0);
}

// ─── Auth API ─────────────────────────────────────────────────────────────────

export interface LoginResult {
  success: boolean;
  user?: AuthUser;
  error?: string;
}

export const authAPI = {
  /**
   * POST /api/v1/auth/login
   * Custom Drupal endpoint that handles both anonymous and already-authenticated
   * sessions — no more 403 "anonymous users only" issues.
   */
  async login(email: string, password: string): Promise<LoginResult> {
    let response: Response;
    try {
      response = await apiFetch('/api/v1/auth/login', {
        method: 'POST',
        body: JSON.stringify({ name: email, pass: password }),
      });
    } catch {
      return { success: false, error: 'Unable to reach the server. Please try again.' };
    }

    if (response.ok) {
      const data = await response.json() as {
        current_user: { uid: string; name: string; roles: string[] };
        csrf_token: string;
        logout_token: string;
      };

      const session: AuthSession = {
        user: {
          uid: data.current_user.uid,
          name: data.current_user.name,
          roles: data.current_user.roles ?? [],
        },
        csrfToken: data.csrf_token,
        logoutToken: data.logout_token,
      };

      saveSession(session);
      return { success: true, user: session.user };
    }

    // 400 — wrong credentials
    if (response.status === 400) {
      let errorMsg = 'Invalid email or password. Please try again.';
      try {
        const err = await response.json() as { message?: string };
        if (err.message) errorMsg = err.message;
      } catch { /* ignore */ }
      return { success: false, error: errorMsg };
    }

    // 403 — account blocked or not yet verified
    if (response.status === 403) {
      return {
        success: false,
        error: 'Your account is not yet verified or has been blocked. Please check your email.',
      };
    }

    return { success: false, error: 'Login failed. Please try again.' };
  },

  /**
   * POST /user/logout?_format=json&token={logoutToken}
   * Invalidates the Drupal session and clears local storage.
   */
  async logout(): Promise<void> {
    const session = getSession();
    if (session?.logoutToken) {
      try {
        await apiFetch(`/user/logout?_format=json&token=${encodeURIComponent(session.logoutToken)}`, {
          method: 'POST',
          headers: { 'X-CSRF-Token': session.csrfToken },
        });
      } catch {
        // Best-effort logout — clear session regardless
      }
    }
    clearSession();
  },

  /**
   * Returns the currently logged-in user from localStorage, or null.
   */
  getCurrentUser(): AuthUser | null {
    return getSession()?.user ?? null;
  },

  /**
   * Returns true if a session exists in localStorage.
   */
  isLoggedIn(): boolean {
    return getSession() !== null;
  },

  /**
   * Returns the stored CSRF token for making authenticated mutations.
   */
  getCsrfToken(): string | null {
    return getSession()?.csrfToken ?? null;
  },

  /**
   * Clears the local session (localStorage + auth cookie) without a network
   * call. Use this when the Drupal session has expired server-side and you
   * need to redirect to login synchronously.
   */
  clearLocalSession(): void {
    clearSession();
  },
};
