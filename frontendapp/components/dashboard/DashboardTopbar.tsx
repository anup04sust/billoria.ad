'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { authAPI } from '@/lib/api/auth';
import './dashboard-topbar.css';

interface DashboardTopbarProps {
  title: string;
  subtitle?: string;
  role?: 'agency' | 'brand' | 'owner' | 'admin';
}

const PROFILE_HREF: Record<string, string> = {
  agency: '/agency/profile',
  brand:  '/brand/profile',
  owner:  '/owner/profile',
  admin:  '/admin/profile',
};

export function DashboardTopbar({ title, subtitle, role }: DashboardTopbarProps) {
  const [user, setUser] = useState<ReturnType<typeof authAPI.getCurrentUser>>(null);

  useEffect(() => {
    setUser(authAPI.getCurrentUser());
  }, []);

  const initials = user?.name
    ? user.name.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase()
    : 'U';

  const profileHref = role ? (PROFILE_HREF[role] ?? '/profile') : '/profile';

  return (
    <header className="db-topbar">
      <div className="db-topbar__left">
        <h1 className="db-topbar__title">{title}</h1>
        {subtitle && <p className="db-topbar__subtitle">{subtitle}</p>}
      </div>

      <div className="db-topbar__right">
        {/* Notifications */}
        <button className="db-topbar__icon-btn" type="button" aria-label="Notifications">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" />
          </svg>
          <span className="db-topbar__badge">3</span>
        </button>

        {/* Divider */}
        <span className="db-topbar__divider" />

        {/* Profile link */}
        <Link href={profileHref} className="db-topbar__profile">
          <div className="db-topbar__avatar">{initials}</div>
          <span className="db-topbar__name">{user?.name ?? 'Account'}</span>
          <svg className="db-topbar__chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <polyline points="4 6 8 10 12 6" />
          </svg>
        </Link>
      </div>
    </header>
  );
}
