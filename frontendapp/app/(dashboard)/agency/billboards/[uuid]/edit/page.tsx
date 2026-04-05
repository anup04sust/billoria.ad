'use client';

import { Suspense, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { BillboardForm } from '@/components/billboard/BillboardForm';
import { authAPI } from '@/lib/api/auth';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';

export default function AgencyBillboardEditPage() {
  const router = useRouter();
  const params = useParams<{ uuid: string }>();

  useEffect(() => {
    if (!authAPI.isLoggedIn()) {
      router.replace('/login');
    }
  }, [router]);

  return (
    <>
      <DashboardSidebar role="agency" />
      <div className="db-layout">
        <DashboardTopbar 
          title="Edit Billboard" 
          subtitle="Update billboard details"
          role="agency"
        />
        <main className="db-main">
          <div className="db-content">
            <Suspense fallback={<div className="loading-state">Loading form...</div>}>
              <BillboardForm
                redirectPath="/agency/billboards"
                role="agency"
                billboardUuid={params.uuid}
              />
            </Suspense>
          </div>
        </main>
      </div>
    </>
  );
}
