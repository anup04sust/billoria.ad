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
import { IconBillboard, IconCalendar, IconClock, IconCurrency, IconChevronUp, IconChevronDown, IconSearch, IconBriefcase, IconUsers, IconBarChart } from '@/lib/icons/ui-icons';

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
                <IconBillboard />
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">24</span>
                <span className="db-stat__label">Active Campaigns</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <IconChevronUp />
                  +3 this month
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--blue">
                <IconCalendar />
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">67</span>
                <span className="db-stat__label">Booked Billboards</span>
                <span className="db-stat__trend db-stat__trend--up">
                  <IconChevronUp />
                  +7 this week
                </span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--amber">
                <IconClock />
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">8</span>
                <span className="db-stat__label">Pending Requests</span>
                <span className="db-stat__trend db-stat__trend--neutral">Awaiting approval</span>
              </div>
            </div>

            <div className="db-stat">
              <div className="db-stat__icon-wrap db-stat__icon-wrap--green">
                <IconCurrency />
              </div>
              <div className="db-stat__body">
                <span className="db-stat__value">৳18.4L</span>
                <span className="db-stat__label">Total Spend (MTD)</span>
                <span className="db-stat__trend db-stat__trend--down">
                  <IconChevronDown />
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
                  <Link href="/agency/billboards" className="db-action-btn db-action-btn--primary">
                    <IconSearch />
                    Own BillBoard
                  </Link>
                  <Link href="/agency/campaigns" className="db-action-btn">
                    <IconBriefcase />
                    New Campaign
                  </Link>
                  <Link href="/agency/clients" className="db-action-btn">
                    <IconUsers />
                    Manage Clients
                  </Link>
                  <Link href="/agency/analytics" className="db-action-btn">
                    <IconBarChart />
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
