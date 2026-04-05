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
import { IconBriefcase, IconBillboard, IconCalendar, IconCurrency, IconChevronUp, IconSearch, IconBarChart } from '@/lib/icons/ui-icons';

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
                <IconBriefcase />
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">6</span>
                <span className="db-stat__label">Active Campaigns</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <IconChevronUp />
                  +2 this month
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--blue">
                <IconBillboard />
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">18</span>
                <span className="db-stat__label">Billboards Booked</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <IconChevronUp />
                  +4 vs last month
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--amber">
                <IconCalendar />
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">3</span>
                <span className="db-stat__label">Renewals This Month</span>
                <span className="db-stat__trend db-stat__trend--neutral">Next: Apr 19</span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--green">
                <IconCurrency />
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
                    <IconSearch />
                    Book a Billboard
                  </Link>
                  <Link href="/brand/campaigns" className="db-action-btn">
                    <IconBriefcase />
                    New Campaign
                  </Link>
                  <Link href="/brand/analytics" className="db-action-btn">
                    <IconBarChart />
                    View Analytics
                  </Link>
                  <Link href="/brand/spend" className="db-action-btn">
                    <IconCurrency />
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
