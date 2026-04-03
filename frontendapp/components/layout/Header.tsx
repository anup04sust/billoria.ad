'use client';

import './header.css';
import { useEffect, useRef, useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { SearchOverlay } from '@/components/shared/SearchOverlay';
import { authAPI } from '@/lib/api/auth';
import { getDashboardRoute } from '@/app/dashboard/page';

function getProfileRoute(roles: string[]): string {
  if (roles.includes('platform_admin')) return '/admin/profile';
  if (roles.includes('agency'))          return '/agency/profile';
  if (roles.includes('billboard_owner')) return '/owner/profile';
  if (roles.includes('brand_user'))      return '/brand/profile';
  return '/dashboard'; // fallback
}

function UserMenu() {
  const router = useRouter();
  const [open, setOpen] = useState(false);
  const [user, setUser] = useState<ReturnType<typeof authAPI.getCurrentUser>>(null);
  const menuRef = useRef<HTMLDivElement>(null);

  // Only read localStorage after hydration to avoid SSR mismatch
  useEffect(() => { setUser(authAPI.getCurrentUser()); }, []);

  // Close on outside click
  useEffect(() => {
    function handleClick(e: MouseEvent) {
      if (menuRef.current && !menuRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  async function handleLogout() {
    setOpen(false);
    await authAPI.logout();
    router.push('/login');
  }

  if (!user) {
    return (
      <Link href="/login" className="site-header__user-btn" aria-label="Sign in">
        <svg className="site-header__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 3a4 4 0 100 8 4 4 0 000-8z" />
        </svg>
      </Link>
    );
  }

  const dashboardHref = getDashboardRoute(user.roles);
  const profileHref = getProfileRoute(user.roles);
  const roles = user.roles;
  const isAgency  = roles.includes('agency');
  const isBrand   = roles.includes('brand_user');
  const isOwner   = roles.includes('billboard_owner');
  const initials  = user.name.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase() || 'U';

  return (
    <div className="site-header__user-menu" ref={menuRef}>
      <button
        className="site-header__user-btn site-header__user-btn--avatar"
        onClick={() => setOpen((v) => !v)}
        aria-label="Account menu"
        aria-expanded={open}
        type="button"
      >
        <span className="site-header__avatar">{initials}</span>
      </button>

      {open && (
        <div className="site-header__dropdown" role="menu">
          {/* User info */}
          <div className="site-header__dropdown-header">
            <span className="site-header__dropdown-name">{user.name}</span>
            <span className="site-header__dropdown-role">
              {isAgency ? 'Agency' : isBrand ? 'Brand' : isOwner ? 'Billboard Owner' : 'Member'}
            </span>
          </div>

          <div className="site-header__dropdown-divider" />

          {/* Dashboard */}
          <Link href={dashboardHref} className="site-header__dropdown-item" onClick={() => setOpen(false)} role="menuitem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
              <rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" />
              <rect x="3" y="14" width="7" height="7" /><rect x="14" y="14" width="7" height="7" />
            </svg>
            Dashboard
          </Link>

          {/* Profile{profileHref}
          <Link href="/profile" className="site-header__dropdown-item" onClick={() => setOpen(false)} role="menuitem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /><circle cx="12" cy="7" r="4" />
            </svg>
            My Profile
          </Link>

          {/* Role-specific page */}
          {isAgency && (
            <Link href="/agency/dashboard" className="site-header__dropdown-item" onClick={() => setOpen(false)} role="menuitem">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
              </svg>
              Agency Portal
            </Link>
          )}
          {isBrand && (
            <Link href="/brand/dashboard" className="site-header__dropdown-item" onClick={() => setOpen(false)} role="menuitem">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
              </svg>
              Brand Portal
            </Link>
          )}
          {isOwner && (
            <Link href="/owner/dashboard" className="site-header__dropdown-item" onClick={() => setOpen(false)} role="menuitem">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
                <rect x="2" y="4" width="20" height="12" rx="1" /><line x1="12" y1="16" x2="12" y2="21" /><line x1="8" y1="21" x2="16" y2="21" />
              </svg>
              Owner Portal
            </Link>
          )}

          <div className="site-header__dropdown-divider" />

          {/* Logout */}
          <button className="site-header__dropdown-item site-header__dropdown-item--danger" onClick={handleLogout} role="menuitem" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
            </svg>
            Sign Out
          </button>
        </div>
      )}
    </div>
  );
}

export function Header() {
  const [isScrolled, setIsScrolled] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      const scrollPosition = window.scrollY;
      setIsScrolled(scrollPosition > 50);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <>
    <header className={`site-header ${isScrolled ? 'is-scrolled' : ''}`}>
      <div className="container">
        <div className="site-header__content">
          {/* Logo */}
          <div className="site-header__logo">
            <a href="/" className="site-header__logo-link">
              <img 
                src="/billoria-logo-evening.svg" 
                alt="Billoria - Billboard Marketplace" 
                className="site-header__logo-image"
              />
            </a>
          </div>

          {/* Actions - Right side */}
          <div className="site-header__actions">
            {/* Search Icon */}
            <button className="site-header__search-btn" aria-label="Search" onClick={() => setSearchOpen(true)}>
              <svg 
                className="site-header__icon" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path 
                  strokeLinecap="round" 
                  strokeLinejoin="round" 
                  strokeWidth={2} 
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" 
                />
              </svg>
            </button>

            {/* User Account */}
            <UserMenu />

            {/* Mobile Menu Button */}
            <button className="site-header__nav-toggle" aria-label="Menu">
              <svg 
                className="site-header__icon" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path 
                  strokeLinecap="round" 
                  strokeLinejoin="round" 
                  strokeWidth={2} 
                  d="M4 6h16M4 12h16M4 18h16" 
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

      <SearchOverlay open={searchOpen} onClose={() => setSearchOpen(false)} />
    </>
  );
}
