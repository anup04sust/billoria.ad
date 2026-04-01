'use client';

import Link from 'next/link';
import Image from 'next/image';
import { useEffect, useState } from 'react';
import { usePathname, useRouter } from 'next/navigation';
import { authAPI } from '@/lib/api/auth';
import './dashboard-sidebar.css';

interface NavItem {
  label: string;
  href: string;
  icon: React.ReactNode;
}

interface DashboardSidebarProps {
  role: 'agency' | 'brand' | 'owner' | 'admin';
}

const ICONS = {
  grid: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" />
      <rect x="3" y="14" width="7" height="7" /><rect x="14" y="14" width="7" height="7" />
    </svg>
  ),
  billboard: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <rect x="2" y="4" width="20" height="12" rx="1" /><line x1="12" y1="16" x2="12" y2="21" /><line x1="8" y1="21" x2="16" y2="21" />
    </svg>
  ),
  calendar: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <rect x="3" y="4" width="18" height="18" rx="2" /><line x1="3" y1="10" x2="21" y2="10" />
      <line x1="8" y1="2" x2="8" y2="6" /><line x1="16" y1="2" x2="16" y2="6" />
    </svg>
  ),
  chart: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <line x1="18" y1="20" x2="18" y2="10" /><line x1="12" y1="20" x2="12" y2="4" /><line x1="6" y1="20" x2="6" y2="14" />
      <line x1="2" y1="20" x2="22" y2="20" />
    </svg>
  ),
  search: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
    </svg>
  ),
  briefcase: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <rect x="2" y="7" width="20" height="14" rx="2" />
      <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
    </svg>
  ),
  coin: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="10" /><path d="M12 6v12M9 9h4.5a2.5 2.5 0 010 5H9m0 0h5" />
    </svg>
  ),
  users: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" />
      <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
    </svg>
  ),
  settings: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="3" />
      <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" />
    </svg>
  ),
  logout: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
      <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
    </svg>
  ),
};

const NAV_BY_ROLE: Record<DashboardSidebarProps['role'], NavItem[]> = {
  agency: [
    { label: 'Overview',        href: '/agency/dashboard', icon: ICONS.grid },
    { label: 'Find Billboards', href: '/billboards',        icon: ICONS.search },
    { label: 'Campaigns',       href: '/agency/campaigns',  icon: ICONS.briefcase },
    { label: 'Bookings',        href: '/agency/bookings',   icon: ICONS.calendar },
    { label: 'Analytics',       href: '/agency/analytics',  icon: ICONS.chart },
    { label: 'Clients',         href: '/agency/clients',    icon: ICONS.users },
    { label: 'Profile',         href: '/agency/profile',    icon: ICONS.users },
    { label: 'Settings',        href: '/agency/settings',   icon: ICONS.settings },
  ],
  brand: [
    { label: 'Overview',        href: '/brand/dashboard',  icon: ICONS.grid },
    { label: 'Find Billboards', href: '/billboards',        icon: ICONS.search },
    { label: 'My Campaigns',    href: '/brand/campaigns',   icon: ICONS.briefcase },
    { label: 'Bookings',        href: '/brand/bookings',    icon: ICONS.calendar },
    { label: 'Analytics',       href: '/brand/analytics',   icon: ICONS.chart },
    { label: 'Spend',           href: '/brand/spend',       icon: ICONS.coin },
    { label: 'Profile',         href: '/brand/profile',     icon: ICONS.users },
    { label: 'Settings',        href: '/brand/settings',    icon: ICONS.settings },
  ],
  owner: [
    { label: 'Overview', href: '/owner/dashboard', icon: ICONS.grid },
    { label: 'My Billboards', href: '/owner/billboards', icon: ICONS.billboard },
    { label: 'Booking Requests', href: '/owner/requests', icon: ICONS.calendar },
    { label: 'Earnings', href: '/owner/earnings', icon: ICONS.coin },
    { label: 'Analytics', href: '/owner/analytics', icon: ICONS.chart },
    { label: 'Settings', href: '/owner/settings', icon: ICONS.settings },
  ],
  admin: [
    { label: 'Overview', href: '/admin/dashboard', icon: ICONS.grid },
    { label: 'Billboards', href: '/admin/billboards', icon: ICONS.billboard },
    { label: 'Users', href: '/admin/users', icon: ICONS.users },
    { label: 'Bookings', href: '/admin/bookings', icon: ICONS.calendar },
    { label: 'Analytics', href: '/admin/analytics', icon: ICONS.chart },
    { label: 'Settings', href: '/admin/settings', icon: ICONS.settings },
  ],
};

const ROLE_LABEL: Record<DashboardSidebarProps['role'], string> = {
  agency: 'Agency',
  brand: 'Brand',
  owner: 'Billboard Owner',
  admin: 'Platform Admin',
};

export function DashboardSidebar({ role }: DashboardSidebarProps) {
  const pathname = usePathname();
  const router = useRouter();
  const [user, setUser] = useState<ReturnType<typeof authAPI.getCurrentUser>>(null);
  const navItems = NAV_BY_ROLE[role];

  useEffect(() => {
    setUser(authAPI.getCurrentUser());
  }, []);

  async function handleLogout() {
    await authAPI.logout();
    router.push('/login');
  }

  return (
    <aside className="db-sidebar">
      {/* Logo */}
      <div className="db-sidebar__logo">
        <Link href="/">
          <Image src="/billoria-logo-white.svg" alt="Billoria" width={208} height={35} priority />
        </Link>
      </div>

      {/* Role badge */}
      <div className="db-sidebar__role-badge">
        <span className="db-sidebar__role-dot" />
        {ROLE_LABEL[role]}
      </div>

      {/* Navigation */}
      <nav className="db-sidebar__nav" aria-label="Dashboard navigation">
        {navItems.map((item) => {
          const active = pathname === item.href || pathname.startsWith(item.href + '/');
          return (
            <Link
              key={item.href}
              href={item.href}
              className={`db-sidebar__link ${active ? 'db-sidebar__link--active' : ''}`}
            >
              <span className="db-sidebar__link-icon">{item.icon}</span>
              <span>{item.label}</span>
              {active && <span className="db-sidebar__link-bar" />}
            </Link>
          );
        })}
      </nav>

      {/* User footer */}
      <div className="db-sidebar__footer">
        <div className="db-sidebar__user">
          <div className="db-sidebar__avatar">
            {user?.name?.charAt(0).toUpperCase() ?? 'U'}
          </div>
          <div className="db-sidebar__user-info">
            <span className="db-sidebar__user-name">{user?.name ?? 'User'}</span>
            <span className="db-sidebar__user-role">{ROLE_LABEL[role]}</span>
          </div>
        </div>
        <button
          className="db-sidebar__logout"
          onClick={handleLogout}
          type="button"
          aria-label="Sign out"
        >
          {ICONS.logout}
        </button>
      </div>
    </aside>
  );
}
