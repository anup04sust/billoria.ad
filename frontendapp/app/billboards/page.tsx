import type { Metadata } from 'next';
import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { Breadcrumb } from '@/components/shared/Breadcrumb';
import { Chatbot } from '@/components/shared/Chatbot';
import { BillboardListPageClient } from '@/components/billboard/BillboardListPageClient';
import { billboardAPI } from '@/lib/api/billboard';
import type { Billboard } from '@/types/billboard';

export const metadata: Metadata = {
  title: 'All Billboards — Billoria',
  description: 'Browse and filter billboards across Bangladesh. Find the perfect outdoor advertising location.',
};

export default async function BillboardsPage() {
  let billboards: Billboard[] = [];
  try {
    const res = await billboardAPI.list({ limit: 500 });
    billboards = res.data.billboards || [];
  } catch {
    // Will show empty state
  }

  return (
    <div className="min-h-screen">
      <Header />
      <main>
        <Breadcrumb items={[
          { label: 'Billboards' },
        ]} />
        <BillboardListPageClient billboards={billboards} />
      </main>
      <Footer />
      <Chatbot />
    </div>
  );
}
