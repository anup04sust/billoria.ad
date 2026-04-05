'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { BillboardInventoryList } from '@/components/billboard/BillboardInventoryList';
import { BillboardInventoryLoading, BillboardInventoryError } from '@/components/billboard/BillboardInventoryStates';
import { authAPI } from '@/lib/api/auth';
import { billboardAPI } from '@/lib/api/billboard';
import type { Billboard } from '@/types/billboard';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';

export default function AdminBillboardsPage() {
  const router = useRouter();
  const [billboards, setBillboards] = useState<Billboard[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!authAPI.isLoggedIn()) {
      router.replace('/login');
      return;
    }

    async function fetchData() {
      try {
        setLoading(true);
        
        // Admin sees ALL billboards in the system
        const billboardsResponse = await billboardAPI.list({
          limit: 1000, // Higher limit for admin
        });
        
        setBillboards(billboardsResponse.data.billboards || []);
      } catch (err) {
        console.error('Error fetching billboards:', err);
        setError('Failed to load billboards. Please try again.');
      } finally {
        setLoading(false);
      }
    }

    fetchData();
  }, [router]);

  return (
    <div className="db-shell">
      <DashboardSidebar role="admin" />
      <div className="db-main">
        <DashboardTopbar
          role="admin"
          title="All Billboards"
          subtitle="Manage all billboard listings on the platform"
        />
        <div className="db-content">
          {loading ? (
            <BillboardInventoryLoading />
          ) : error ? (
            <BillboardInventoryError message={error} />
          ) : (
            <BillboardInventoryList 
              billboards={billboards}
              organizationName="Platform-wide"
              showStats={true}
              basePath="/admin/billboards"
            />
          )}
        </div>
      </div>
    </div>
  );
}
