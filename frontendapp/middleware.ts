import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

// Routes that require authentication
const PROTECTED_PREFIXES = [
  '/dashboard',
  '/agency',
  '/brand',
  '/owner',
  '/admin',
  '/logout',
];

// Routes that authenticated users should not access
const AUTH_ONLY_ROUTES = ['/login', '/register'];

const AUTH_COOKIE = 'billoria_logged_in';

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const isLoggedIn = request.cookies.has(AUTH_COOKIE) &&
    request.cookies.get(AUTH_COOKIE)?.value === '1';

  // Redirect logged-in users away from login/register, preserving ?next=
  if (isLoggedIn && AUTH_ONLY_ROUTES.some((p) => pathname.startsWith(p))) {
    const next = request.nextUrl.searchParams.get('next');
    // Validate next is a safe relative path (prevent open-redirect)
    const destination = next && /^\/[^/]/.test(next) ? next : '/dashboard';
    return NextResponse.redirect(new URL(destination, request.url));
  }

  // Redirect unauthenticated users away from protected routes
  if (!isLoggedIn && PROTECTED_PREFIXES.some((p) => pathname.startsWith(p))) {
    const loginUrl = new URL('/login', request.url);
    loginUrl.searchParams.set('next', pathname);
    return NextResponse.redirect(loginUrl);
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    /*
     * Match all paths except:
     * - _next/static, _next/image (Next.js internals)
     * - public files with an extension (images, fonts, etc.)
     * - api routes
     */
    '/((?!_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp|ico|css|js|woff2?|ttf|eot)).*)',
  ],
};
