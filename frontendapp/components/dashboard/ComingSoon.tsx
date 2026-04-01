'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { authAPI } from '@/lib/api/auth';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';

interface ComingSoonPageProps {
  role: 'agency' | 'brand' | 'owner' | 'admin';
  title: string;
  subtitle?: string;
  icon?: React.ReactNode;
}

export function ComingSoonPage({ role, title, subtitle, icon }: ComingSoonPageProps) {
  const router = useRouter();

  useEffect(() => {
    if (!authAPI.isLoggedIn()) router.replace('/login');
  }, [router]);

  return (
    <div className="db-shell">
      <DashboardSidebar role={role} />
      <div className="db-main">
        <DashboardTopbar role={role} title={title} subtitle={subtitle} />
        <div className="db-content">
          <div className="db-panel" style={{ textAlign: 'center', padding: '4rem 2rem' }}>
            <div style={{
              width: '3.5rem', height: '3.5rem',
              borderRadius: '50%',
              background: 'rgba(52, 211, 153, 0.12)',
              color: '#059669',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              margin: '0 auto 1.25rem',
            }}>
              {icon ?? (
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75"
                  strokeLinecap="round" strokeLinejoin="round" style={{ width: '1.5rem', height: '1.5rem' }}>
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
              )}
            </div>
            <h2 style={{ fontSize: '1.25rem', fontWeight: 700, color: 'var(--color-gray-900)', margin: '0 0 0.5rem' }}>
              Development in Progress
            </h2>
            <p style={{ fontSize: '0.9375rem', color: 'var(--color-gray-500)', margin: 0, maxWidth: '420px', marginInline: 'auto' }}>
              <strong>{title}</strong> is currently being built. Check back soon — this feature will be available in an upcoming release.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
