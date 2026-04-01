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

const RECENT_BOOKINGS = [
  { id: 'BK-1041', billboard: 'Gulshan-2 Junction Billboard', location: 'Dhaka', period: 'Apr 1 – Apr 30', status: 'active', amount: '৳120,000' },
  { id: 'BK-1038', billboard: 'Banani Flyover LED', location: 'Dhaka', period: 'Mar 15 – Mar 31', status: 'completed', amount: '৳85,000' },
  { id: 'BK-1036', billboard: 'CDA Avenue Unipole', location: 'Chittagong', period: 'Apr 5 – May 4', status: 'pending', amount: '৳65,000' },
  { id: 'BK-1033', billboard: 'Sylhet Station Road', location: 'Sylhet', period: 'Apr 1 – Apr 14', status: 'active', amount: '৳42,000' },
];

const STATUS_PILL: Record<string, string> = {
  active: 'db-pill--green',
  completed: 'db-pill--gray',
  pending: 'db-pill--amber',
  cancelled: 'db-pill--red',
};

export default function AgencyDashboardPage() {
  const router = useRouter();

  useEffect(() => {
    if (!authAPI.isLoggedIn()) router.replace('/login');
  }, [router]);

  return (
    <div className="db-shell">
      <DashboardSidebar role="agency" />
      <div className="db-main">
        <DashboardTopbar
          role="agency"
          title="Agency Dashboard"
          subtitle="Manage campaigns, bookings and client performance"
        />
        <div className="db-content">

          {/* Stats */}
          <div className="db-stats">
            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="4" width="20" height="12" rx="1" /><line x1="12" y1="16" x2="12" y2="21" /><line x1="8" y1="21" x2="16" y2="21" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">24</span>
                <span className="db-stat__label">Active Campaigns</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="18 15 12 9 6 15" /></svg>
                  +3 this month
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="3" y1="10" x2="21" y2="10" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="16" y1="2" x2="16" y2="6" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">67</span>
                <span className="db-stat__label">Booked Billboards</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="18 15 12 9 6 15" /></svg>
                  +7 this week
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">8</span>
                <span className="db-stat__label">Pending Requests</span>
                <span className="db-stat__trend db-stat__trend--neutral">Awaiting approval</span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10" /><path d="M12 6v12M9 9h4.5a2.5 2.5 0 010 5H9m0 0h5" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">৳18.4L</span>
                <span className="db-stat__label">Total Spend (MTD)</span>
                <span className="db-stat__trend db-stat__trend--down">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="6 9 12 15 18 9" /></svg>
                  -5% vs last month
                </span>
              </div>
            </div>
          </div>

          {/* Main grid */}
          <div className="db-grid-2">
            {/* Recent bookings */}
            <div className="db-panel">
              <div className="db-panel__head">
                <h2 className="db-panel__title">Recent Bookings</h2>
                <Link href="/agency/bookings" className="db-panel__link">View all</Link>
              </div>
              <div className="db-table-wrap">
                <table className="db-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Billboard</th>
                      <th>Period</th>
                      <th>Status</th>
                      <th>Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    {RECENT_BOOKINGS.map((b) => (
                      <tr key={b.id}>
                        <td style={{ fontFamily: 'var(--font-geist-mono, monospace)', fontSize: '0.8125rem', color: 'var(--color-gray-500)' }}>{b.id}</td>
                        <td>
                          <div style={{ fontWeight: 500, color: 'var(--color-gray-900)' }}>{b.billboard}</div>
                          <div style={{ fontSize: '0.75rem', color: 'var(--color-gray-500)' }}>{b.location}</div>
                        </td>
                        <td style={{ fontSize: '0.8125rem' }}>{b.period}</td>
                        <td><span className={`db-pill ${STATUS_PILL[b.status]}`}>{b.status}</span></td>
                        <td style={{ fontWeight: 600, color: 'var(--color-gray-900)' }}>{b.amount}</td>
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
                    Find Billboards
                  </Link>
                  <Link href="/agency/campaigns" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" /></svg>
                    New Campaign
                  </Link>
                  <Link href="/agency/clients" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" /></svg>
                    Manage Clients
                  </Link>
                  <Link href="/agency/analytics" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><line x1="18" y1="20" x2="18" y2="10" /><line x1="12" y1="20" x2="12" y2="4" /><line x1="6" y1="20" x2="6" y2="14" /><line x1="2" y1="20" x2="22" y2="20" /></svg>
                    View Analytics
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
