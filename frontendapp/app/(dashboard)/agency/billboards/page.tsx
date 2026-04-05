'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { BillboardInventoryList } from '@/components/billboard/BillboardInventoryList';
import { BillboardInventoryLoading, BillboardInventoryError } from '@/components/billboard/BillboardInventoryStates';
import { authAPI } from '@/lib/api/auth';
import { billboardAPI } from '@/lib/api/billboard';
import { profileAPI } from '@/lib/api/profile';
import type { Billboard } from '@/types/billboard';
import type { ProfileOrganization } from '@/lib/api/profile';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';

export default function AgencyBillboardsPage() {
  const router = useRouter();
  const [billboards, setBillboards] = useState<Billboard[]>([]);
  const [organization, setOrganization] = useState<ProfileOrganization | null>(null);
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
        
        // Get user profile to find organization name
        const profile = await profileAPI.get();
        const agencyOrg = profile.organizations.find(
          org => org.type === 'agency'
        );
        
        if (agencyOrg) {
          setOrganization(agencyOrg);
        }

        // Fetch billboards using the dedicated my-billboards endpoint
        // This automatically filters by user's organization
        const billboardsResponse = await billboardAPI.myBillboards({
          limit: 500,
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
      <DashboardSidebar role="agency" />
      <div className="db-main">
        <DashboardTopbar
          role="agency"
          title="Own Billboards"
          subtitle="Manage your billboard inventory"
        />
        <div className="db-content">
          {loading ? (
            <BillboardInventoryLoading />
          ) : error ? (
            <BillboardInventoryError message={error} />
          ) : (
            <BillboardInventoryList 
              billboards={billboards}
              organizationName={organization?.name}
              showStats={true}
              basePath="/agency/billboards"
            />
          )}
        </div>
      </div>
    </div>
  );
}
