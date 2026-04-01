'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { authAPI } from '@/lib/api/auth';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';

const RECENT_CAMPAIGNS = [
  { id: 'CP-304', name: 'Eid Collection 2026', billboards: 5, period: 'Apr 1 – Apr 30', status: 'active', budget: '৳3,50,000' },
  { id: 'CP-301', name: 'New Store Launch — Sylhet', billboards: 3, period: 'Mar 20 – Apr 19', status: 'active', budget: '৳1,80,000' },
  { id: 'CP-298', name: 'Brand Awareness Q1', billboards: 8, period: 'Jan – Mar', status: 'completed', budget: '৳6,40,000' },
  { id: 'CP-295', name: 'Summer Sale Teasers', billboards: 2, period: 'May 1 – May 31', status: 'pending', budget: '৳90,000' },
];

const STATUS_PILL: Record<string, string> = {
  active: 'db-pill--green',
  completed: 'db-pill--gray',
  pending: 'db-pill--amber',
  cancelled: 'db-pill--red',
};

export default function BrandDashboardPage() {
  const router = useRouter();

  useEffect(() => {
    if (!authAPI.isLoggedIn()) router.replace('/login');
  }, [router]);

  return (
    <div className="db-shell">
      <DashboardSidebar role="brand" />
      <div className="db-main">
        <DashboardTopbar
          role="brand"
          title="Brand Dashboard"
          subtitle="Track your campaigns and advertising performance"
        />
        <div className="db-content">

          {/* Stats */}
          <div className="db-stats">
            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">6</span>
                <span className="db-stat__label">Active Campaigns</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="18 15 12 9 6 15" /></svg>
                  +2 this month
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="4" width="20" height="12" rx="1" /><line x1="12" y1="16" x2="12" y2="21" /><line x1="8" y1="21" x2="16" y2="21" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">18</span>
                <span className="db-stat__label">Billboards Booked</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="18 15 12 9 6 15" /></svg>
                  +4 vs last month
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="3" y1="10" x2="21" y2="10" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="16" y1="2" x2="16" y2="6" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">3</span>
                <span className="db-stat__label">Renewals This Month</span>
                <span className="db-stat__trend db-stat__trend--neutral">Next: Apr 19</span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10" /><path d="M12 6v12M9 9h4.5a2.5 2.5 0 010 5H9m0 0h5" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">৳11.6L</span>
                <span className="db-stat__label">Budget Spent (YTD)</span>
                <span className="db-stat__trend db-stat__trend--neutral">62% of annual</span>
              </div>
            </div>
          </div>

          {/* Main grid */}
          <div className="db-grid-2">
            {/* Campaigns */}
            <div className="db-panel">
              <div className="db-panel__head">
                <h2 className="db-panel__title">My Campaigns</h2>
                <Link href="/brand/campaigns" className="db-panel__link">View all</Link>
              </div>
              <div className="db-table-wrap">
                <table className="db-table">
                  <thead>
                    <tr>
                      <th>Campaign</th>
                      <th>Billboards</th>
                      <th>Period</th>
                      <th>Status</th>
                      <th>Budget</th>
                    </tr>
                  </thead>
                  <tbody>
                    {RECENT_CAMPAIGNS.map((c) => (
                      <tr key={c.id}>
                        <td>
                          <div style={{ fontWeight: 500, color: 'var(--color-gray-900)' }}>{c.name}</div>
                          <div style={{ fontSize: '0.75rem', color: 'var(--color-gray-500)', fontFamily: 'var(--font-geist-mono, monospace)' }}>{c.id}</div>
                        </td>
                        <td style={{ textAlign: 'center' }}>{c.billboards}</td>
                        <td style={{ fontSize: '0.8125rem' }}>{c.period}</td>
                        <td><span className={`db-pill ${STATUS_PILL[c.status]}`}>{c.status}</span></td>
                        <td style={{ fontWeight: 600, color: 'var(--color-gray-900)' }}>{c.budget}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            {/* Quick actions */}
            <div className="db-panel">
              <div className="db-panel__head">
                <h2 className="db-panel__title">Quick Actions</h2>
              </div>
              <div className="db-panel__body">
                <div className="db-actions">
                  <Link href="/billboards" className="db-action-btn db-action-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
                    Book a Billboard
                  </Link>
                  <Link href="/brand/campaigns" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" /></svg>
                    New Campaign
                  </Link>
                  <Link href="/brand/analytics" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><line x1="18" y1="20" x2="18" y2="10" /><line x1="12" y1="20" x2="12" y2="4" /><line x1="6" y1="20" x2="6" y2="14" /><line x1="2" y1="20" x2="22" y2="20" /></svg>
                    View Analytics
                  </Link>
                  <Link href="/brand/spend" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10" /><path d="M12 6v12M9 9h4.5a2.5 2.5 0 010 5H9m0 0h5" /></svg>
                    Budget & Spend
                  </Link>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  );
}
