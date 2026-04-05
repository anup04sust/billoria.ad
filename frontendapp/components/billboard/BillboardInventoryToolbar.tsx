'use client';

import { IconSearch, IconGrid, IconList } from '@/lib/icons/ui-icons';
import './billboard-inventory.css';

export type SortKey = 'newest' | 'price-asc' | 'price-desc' | 'name';
export type StatusFilter = 'all' | 'available' | 'booked' | 'maintenance';
export type ReviewFilter = 'all' | 'draft' | 'pending_review' | 'approved' | 'revision_requested' | 'rejected';
export type ViewMode = 'grid' | 'table';

interface BillboardInventoryToolbarProps {
  searchQuery: string;
  onSearchChange: (value: string) => void;
  statusFilter: StatusFilter;
  onStatusFilterChange: (value: StatusFilter) => void;
  reviewFilter: ReviewFilter;
  onReviewFilterChange: (value: ReviewFilter) => void;
  sortBy: SortKey;
  onSortChange: (value: SortKey) => void;
  viewMode: ViewMode;
  onViewModeChange: (value: ViewMode) => void;
}

export function BillboardInventoryToolbar({
  searchQuery,
  onSearchChange,
  statusFilter,
  onStatusFilterChange,
  reviewFilter,
  onReviewFilterChange,
  sortBy,
  onSortChange,
  viewMode,
  onViewModeChange,
}: BillboardInventoryToolbarProps) {
  return (
    <div className="bi-toolbar">
      <div className="bi-search">
        <IconSearch />
        <input
          type="text"
          placeholder="Search billboards..."
          value={searchQuery}
          onChange={(e) => onSearchChange(e.target.value)}
        />
      </div>

      <div className="bi-filters">
        <select 
          value={statusFilter} 
          onChange={(e) => onStatusFilterChange(e.target.value as StatusFilter)}
        >
          <option value="all">All Status</option>
          <option value="available">Available</option>
          <option value="booked">Booked</option>
          <option value="maintenance">Maintenance</option>
        </select>

        <select
          value={reviewFilter}
          onChange={(e) => onReviewFilterChange(e.target.value as ReviewFilter)}
        >
          <option value="all">All Reviews</option>
          <option value="draft">Draft</option>
          <option value="pending_review">Pending Review</option>
          <option value="approved">Approved</option>
          <option value="revision_requested">Revision Requested</option>
          <option value="rejected">Rejected</option>
        </select>

        <select 
          value={sortBy} 
          onChange={(e) => onSortChange(e.target.value as SortKey)}
        >
          <option value="newest">Newest First</option>
          <option value="name">Name A-Z</option>
          <option value="price-asc">Price: Low to High</option>
          <option value="price-desc">Price: High to Low</option>
        </select>

        <div className="bi-view-toggle">
          <button
            className={viewMode === 'grid' ? 'active' : ''}
            onClick={() => onViewModeChange('grid')}
            aria-label="Grid view"
            type="button"
          >
            <IconGrid />
          </button>
          <button
            className={viewMode === 'table' ? 'active' : ''}
            onClick={() => onViewModeChange('table')}
            aria-label="Table view"
            type="button"
          >
            <IconList />
          </button>
        </div>
      </div>
    </div>
  );
}
