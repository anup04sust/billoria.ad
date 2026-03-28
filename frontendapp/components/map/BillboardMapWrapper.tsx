'use client';

import dynamic from 'next/dynamic';
import type { Billboard } from '@/types/billboard';
import { MapSkeleton } from './MapSkeleton';

// Dynamically import map component to avoid SSR issues with Leaflet
const BillboardMap = dynamic(
  () => import('./BillboardMap').then(mod => ({ default: mod.BillboardMap })),
  { 
    ssr: false,
    loading: () => <MapSkeleton />
  }
);

interface BillboardMapWrapperProps {
  billboards?: Billboard[];
}

export function BillboardMapWrapper({ billboards = [] }: BillboardMapWrapperProps) {
  return <BillboardMap billboards={billboards} />;
}
