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

const BOOKING_REQUESTS = [
  { id: 'REQ-881', billboard: 'Mirpur 10 Roundabout', requester: 'OmniAds Agency', period: 'May 1 – May 31', status: 'pending', amount: '৳95,000' },
  { id: 'REQ-879', billboard: 'Uttara Sector 7 Unipole', requester: 'FreshBrand Ltd.', period: 'Apr 15 – May 14', status: 'approved', amount: '৳72,000' },
  { id: 'REQ-876', billboard: 'Dhanmondi 27 Wall', requester: 'MediaLink BD', period: 'Apr 1 – Apr 30', status: 'approved', amount: '৳55,000' },
  { id: 'REQ-872', billboard: 'Wari Junction LED', requester: 'GoalAds Inc.', period: 'Mar 15 – Apr 14', status: 'completed', amount: '৳88,000' },
];

const STATUS_PILL: Record<string, string> = {
  approved: 'db-pill--green',
  completed: 'db-pill--blue',
  pending: 'db-pill--amber',
  rejected: 'db-pill--red',
};

export default function OwnerDashboardPage() {
  const router = useRouter();

  useEffect(() => {
    if (!authAPI.isLoggedIn()) router.replace('/login');
  }, [router]);

  return (
    <div className="db-shell">
      <DashboardSidebar role="owner" />
      <div className="db-main">
        <DashboardTopbar
          role="owner"
          title="Owner Dashboard"
          subtitle="Manage your billboards and track earnings"
        />
        <div className="db-content">

          {/* Stats */}
          <div className="db-stats">
            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="4" width="20" height="12" rx="1" /><line x1="12" y1="16" x2="12" y2="21" /><line x1="8" y1="21" x2="16" y2="21" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">14</span>
                <span className="db-stat__label">Total Billboards</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="18 15 12 9 6 15" /></svg>
                  +2 added this quarter
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--red">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="3" y1="10" x2="21" y2="10" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="16" y1="2" x2="16" y2="6" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">9</span>
                <span className="db-stat__label">Active Bookings</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="18 15 12 9 6 15" /></svg>
                  64% occupancy
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">4</span>
                <span className="db-stat__label">Pending Requests</span>
                <span className="db-stat__trend db-stat__trend--neutral">Needs your review</span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10" /><path d="M12 6v12M9 9h4.5a2.5 2.5 0 010 5H9m0 0h5" /></svg>
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">৳4.2L</span>
                <span className="db-stat__label">Revenue This Month</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="18 15 12 9 6 15" /></svg>
                  +12% vs last month
                </span>
              </div>
            </div>
          </div>

          {/* Main grid */}
          <div className="db-grid-2">
            {/* Booking requests */}
            <div className="db-panel">
              <div className="db-panel__head">
                <h2 className="db-panel__title">Booking Requests</h2>
                <Link href="/owner/requests" className="db-panel__link">View all</Link>
              </div>
              <div className="db-table-wrap">
                <table className="db-table">
                  <thead>
                    <tr>
                      <th>Billboard</th>
                      <th>Requester</th>
                      <th>Period</th>
                      <th>Status</th>
                      <th>Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    {BOOKING_REQUESTS.map((r) => (
                      <tr key={r.id}>
                        <td>
                          <div style={{ fontWeight: 500, color: 'var(--color-gray-900)' }}>{r.billboard}</div>
                          <div style={{ fontSize: '0.75rem', color: 'var(--color-gray-500)', fontFamily: 'var(--font-geist-mono, monospace)' }}>{r.id}</div>
                        </td>
                        <td style={{ fontSize: '0.8125rem' }}>{r.requester}</td>
                        <td style={{ fontSize: '0.8125rem' }}>{r.period}</td>
                        <td><span className={`db-pill ${STATUS_PILL[r.status]}`}>{r.status}</span></td>
                        <td style={{ fontWeight: 600, color: 'var(--color-gray-900)' }}>{r.amount}</td>
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
                  <Link href="/owner/billboards/new" className="db-action-btn db-action-btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="16" /><line x1="8" y1="12" x2="16" y2="12" /></svg>
                    Add Billboard
                  </Link>
                  <Link href="/owner/billboards" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="4" width="20" height="12" rx="1" /><line x1="12" y1="16" x2="12" y2="21" /><line x1="8" y1="21" x2="16" y2="21" /></svg>
                    Manage Listings
                  </Link>
                  <Link href="/owner/requests" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" /><line x1="3" y1="10" x2="21" y2="10" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="16" y1="2" x2="16" y2="6" /></svg>
                    Review Requests
                  </Link>
                  <Link href="/owner/earnings" className="db-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10" /><path d="M12 6v12M9 9h4.5a2.5 2.5 0 010 5H9m0 0h5" /></svg>
                    View Earnings
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
