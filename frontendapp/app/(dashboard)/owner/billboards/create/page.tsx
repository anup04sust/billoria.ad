'use client';

import { Suspense, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { DashboardSidebar } from '@/components/dashboard/DashboardSidebar';
import { DashboardTopbar } from '@/components/dashboard/DashboardTopbar';
import { BillboardForm } from '@/components/billboard/BillboardForm';
import { authAPI } from '@/lib/api/auth';
import '@/components/dashboard/dashboard-sidebar.css';
import '@/components/dashboard/dashboard-topbar.css';
import '@/components/dashboard/dashboard.css';

export default function OwnerBillboardCreatePage() {
  const router = useRouter();

  useEffect(() => {
    if (!authAPI.isLoggedIn()) {
      router.replace('/login');
    }
  }, [router]);

  return (
    <>
      <DashboardSidebar role="owner" />
      <div className="db-layout">
        <DashboardTopbar 
          title="Create New Billboard" 
          subtitle="Add a new billboard to your inventory"
          role="owner"
        />
        <main className="db-main">
          <div className="db-content">
            <Suspense fallback={<div className="loading-state">Loading form...</div>}>
              <BillboardForm redirectPath="/owner/billboards" />
            </Suspense>
          </div>
        </main>
      </div>
    </>
  );
}
