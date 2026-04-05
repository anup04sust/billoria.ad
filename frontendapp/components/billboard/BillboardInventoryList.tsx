'use client';

import { useState, useMemo } from 'react';
import type { Billboard } from '@/types/billboard';
import { BillboardInventoryStats } from './BillboardInventoryStats';
import { BillboardInventoryToolbar, type SortKey, type StatusFilter, type ReviewFilter, type ViewMode } from './BillboardInventoryToolbar';
import { BillboardInventoryCard } from './BillboardInventoryCard';
import { BillboardInventoryTable } from './BillboardInventoryTable';
import { IconBillboardAlt } from '@/lib/icons/ui-icons';
import './billboard-inventory.css';

interface BillboardInventoryListProps {
  billboards: Billboard[];
  organizationName?: string;
  showStats?: boolean;
  basePath?: string;
}

export function BillboardInventoryList({ 
  billboards, 
  organizationName,
  showStats = true,
  basePath,
}: BillboardInventoryListProps) {
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState<StatusFilter>('all');
  const [reviewFilter, setReviewFilter] = useState<ReviewFilter>('all');
  const [sortBy, setSortBy] = useState<SortKey>('newest');
  const [viewMode, setViewMode] = useState<ViewMode>('table');

  // Apply filters and sorting
  const processedBillboards = useMemo(() => {
    let filtered = [...billboards];

    // Search filter
    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      filtered = filtered.filter(b =>
        b.title?.toLowerCase().includes(query) ||
        b.billboard_id?.toLowerCase().includes(query) ||
        b.area_zone?.label?.toLowerCase().includes(query) ||
        b.district?.label?.toLowerCase().includes(query)
      );
    }

    // Status filter
    if (statusFilter !== 'all') {
      filtered = filtered.filter(b => {
        const status = b.availability_status?.label?.toLowerCase();
        return status === statusFilter;
      });
    }

    // Review status filter
    if (reviewFilter !== 'all') {
      filtered = filtered.filter(b => (b.review_status || 'draft') === reviewFilter);
    }

    // Sorting
    switch (sortBy) {
      case 'price-asc':
        filtered.sort((a, b) => (Number(a.rate_card_price) || 0) - (Number(b.rate_card_price) || 0));
        break;
      case 'price-desc':
        filtered.sort((a, b) => (Number(b.rate_card_price) || 0) - (Number(a.rate_card_price) || 0));
        break;
      case 'name':
        filtered.sort((a, b) => a.title.localeCompare(b.title));
        break;
      case 'newest':
      default:
        filtered.sort((a, b) => b.updated - a.updated);
        break;
    }

    return filtered;
  }, [billboards, searchQuery, statusFilter, reviewFilter, sortBy]);

  return (
    <>
      {showStats && <BillboardInventoryStats billboards={billboards} />}

      <BillboardInventoryToolbar
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        statusFilter={statusFilter}
        onStatusFilterChange={setStatusFilter}
        reviewFilter={reviewFilter}
        onReviewFilterChange={setReviewFilter}
        sortBy={sortBy}
        onSortChange={setSortBy}
        viewMode={viewMode}
        onViewModeChange={setViewMode}
      />

      <div className="bi-results-count">
        Showing <strong>{processedBillboards.length}</strong> of <strong>{billboards.length}</strong> billboards
        {organizationName && <span className="bi-org-name"> • {organizationName}</span>}
      </div>

      {processedBillboards.length === 0 ? (
        <div className="bi-empty">
          <IconBillboardAlt />
          <h3>No billboards found</h3>
          <p>Try adjusting your search or filters</p>
        </div>
      ) : viewMode === 'grid' ? (
        <div className="bi-grid">
          {processedBillboards.map((billboard) => (
            <BillboardInventoryCard key={billboard.id} billboard={billboard} basePath={basePath} />
          ))}
        </div>
      ) : (
        <BillboardInventoryTable billboards={processedBillboards} basePath={basePath} />
      )}
    </>
  );
}
