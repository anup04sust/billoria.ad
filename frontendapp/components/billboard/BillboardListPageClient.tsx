'use client';

import { useState, useMemo } from 'react';
import { useSearchParams } from 'next/navigation';
import type { Billboard } from '@/types/billboard';
import { BillboardListFilters, EMPTY_LIST_FILTERS, TIER_AREAS, type ListFilters } from './BillboardListFilters';
import { BillboardListCard } from './BillboardListCard';
import { SponsoredCarousel } from './SponsoredCarousel';
import { FeaturedBillboards } from './FeaturedBillboards';
import '@/components/billboard/billboard-list-page.css';

const PER_PAGE = 12;

type SortKey = 'newest' | 'price-asc' | 'price-desc' | 'name';

function sortBillboards(billboards: Billboard[], sort: SortKey): Billboard[] {
  const arr = [...billboards];
  switch (sort) {
    case 'price-asc':
      return arr.sort((a, b) => (Number(a.rate_card_price) || 0) - (Number(b.rate_card_price) || 0));
    case 'price-desc':
      return arr.sort((a, b) => (Number(b.rate_card_price) || 0) - (Number(a.rate_card_price) || 0));
    case 'name':
      return arr.sort((a, b) => a.title.localeCompare(b.title));
    default:
      return arr.sort((a, b) => b.updated - a.updated);
  }
}

function applyFilters(billboards: Billboard[], filters: ListFilters): Billboard[] {
  return billboards.filter((b) => {
    if (filters.search) {
      const q = filters.search.toLowerCase();
      const match =
        b.title?.toLowerCase().includes(q) ||
        b.billboard_id?.toLowerCase().includes(q) ||
        b.area_zone?.label?.toLowerCase().includes(q) ||
        b.district?.label?.toLowerCase().includes(q);
      if (!match) return false;
    }
    if (filters.divisions.length > 0 && !filters.divisions.includes(b.division?.label || '')) return false;
    if (filters.districts.length > 0 && !filters.districts.includes(b.district?.label || '')) return false;
    if (filters.mediaFormats.length > 0 && !filters.mediaFormats.includes(b.media_format?.label || '')) return false;
    if (filters.placementTypes.length > 0 && !filters.placementTypes.includes(b.placement_type?.label || '')) return false;
    if (filters.roadTypes.length > 0 && !filters.roadTypes.includes(b.road_type?.label || '')) return false;
    if (filters.statuses.length > 0 && !filters.statuses.includes(b.availability_status?.label || '')) return false;
    if (filters.tiers.length > 0) {
      const zone = b.area_zone?.label || b.district?.label || '';
      const matchesTier = filters.tiers.some((tier) =>
        TIER_AREAS[tier]?.some((area) => zone.toLowerCase().includes(area.toLowerCase()))
      );
      if (!matchesTier) return false;
    }
    if (filters.premiumOnly && b.is_premium !== '1') return false;
    if (filters.priceRange[0] !== null && (Number(b.rate_card_price) || 0) < filters.priceRange[0]) return false;
    if (filters.priceRange[1] !== null && (Number(b.rate_card_price) || 0) > filters.priceRange[1]) return false;
    return true;
  });
}

interface BillboardListPageClientProps {
  billboards: Billboard[];
}

export function BillboardListPageClient({ billboards }: BillboardListPageClientProps) {
  const searchParams = useSearchParams();

  // Build initial filters from URL params
  const initialFilters = useMemo<ListFilters>(() => {
    const f = { ...EMPTY_LIST_FILTERS };
    const division = searchParams.get('division');
    const district = searchParams.get('district');
    const zone = searchParams.get('zone');
    const format = searchParams.get('format');
    const q = searchParams.get('q');
    if (division) f.divisions = [division];
    if (district) f.districts = [district];
    if (zone) f.search = zone;
    if (format) f.mediaFormats = [format];
    if (q) f.search = q;
    return f;
  }, [searchParams]);

  const [filters, setFilters] = useState<ListFilters>(initialFilters);
  const [sort, setSort] = useState<SortKey>('newest');
  const [page, setPage] = useState(1);
  const [mobileFiltersOpen, setMobileFiltersOpen] = useState(false);

  const filtered = useMemo(() => sortBillboards(applyFilters(billboards, filters), sort), [billboards, filters, sort]);
  const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const paginated = filtered.slice((page - 1) * PER_PAGE, page * PER_PAGE);

  // Sponsored: billboards marked as sponsored and available for booking
  const sponsored = useMemo(() => billboards.filter((b) => b.is_sponsored === '1' && b.availability_status?.label?.toLowerCase() === 'available').slice(0, 6), [billboards]);

  // Featured: billboards marked as featured (excluded from regular list)
  const featured = useMemo(() => filtered.filter((b) => b.is_featured === '1').slice(0, 2), [filtered]);
  const featuredIds = useMemo(() => new Set(featured.map((b) => b.id)), [featured]);

  // Reset to page 1 when filters or sort change
  const handleFilterChange = (f: ListFilters) => { setFilters(f); setPage(1); };
  const handleSortChange = (s: SortKey) => { setSort(s); setPage(1); };

  const pageNumbers = useMemo(() => {
    const pages: number[] = [];
    const start = Math.max(1, page - 2);
    const end = Math.min(totalPages, page + 2);
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
  }, [page, totalPages]);

  return (
    <div className="container">
      {/* Toolbar */}
      <div className="bl-page__toolbar">
        <h1 className="bl-page__title">All Billboards</h1>
        <div style={{ display: 'flex', gap: '0.75rem', alignItems: 'center' }}>
          {/* Mobile filter toggle */}
          <button
            className="bl-filters__mobile-toggle"
            onClick={() => setMobileFiltersOpen(!mobileFiltersOpen)}
            type="button"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
              <line x1="4" y1="6" x2="20" y2="6" /><line x1="4" y1="12" x2="14" y2="12" /><line x1="4" y1="18" x2="10" y2="18" />
            </svg>
            Filters
          </button>
          <div className="bl-page__sort">
            <span>Sort by</span>
            <select value={sort} onChange={(e) => handleSortChange(e.target.value as SortKey)}>
              <option value="newest">Newest</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="name">Name A–Z</option>
            </select>
          </div>
        </div>
      </div>

      {/* Layout: sidebar + results */}
      <div className="bl-page__layout">
        <BillboardListFilters
          billboards={billboards}
          filters={filters}
          onChange={handleFilterChange}
          resultCount={filtered.length}
        />

        {/* Mobile overlay filter */}
        {mobileFiltersOpen && (
          <div className="bl-filters bl-filters--open">
            <BillboardListFilters
              billboards={billboards}
              filters={filters}
              onChange={(f) => { handleFilterChange(f); }}
              resultCount={filtered.length}
            />
            <button
              style={{ margin: '0.75rem', padding: '0.75rem', background: 'var(--color-primary)', color: '#fff', border: 'none', borderRadius: 'var(--radius-md)', fontWeight: 600, fontSize: '0.875rem' }}
              onClick={() => setMobileFiltersOpen(false)}
              type="button"
            >
              Show {filtered.length} results
            </button>
          </div>
        )}

        <div className="bl-page__results">
          {/* Sponsored carousel */}
          {page === 1 && <SponsoredCarousel billboards={sponsored} />}

          {/* Featured billboards */}
          {page === 1 && <FeaturedBillboards billboards={featured} />}

          {/* Regular results */}
          {paginated.filter((b) => !featuredIds.has(b.id)).length > 0 ? (
            paginated.filter((b) => !featuredIds.has(b.id)).map((b) => <BillboardListCard key={b.id} billboard={b} />)
          ) : (
            <div className="bl-page__empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
              </svg>
              <p>No billboards match your filters</p>
            </div>
          )}

          {/* Pagination */}
          {totalPages > 1 && (
            <nav className="bl-page__pagination" aria-label="Pagination">
              <button
                className="bl-page__page-btn"
                disabled={page === 1}
                onClick={() => setPage(page - 1)}
                aria-label="Previous page"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><polyline points="15 18 9 12 15 6" /></svg>
              </button>
              {pageNumbers[0] > 1 && (
                <>
                  <button className="bl-page__page-btn" onClick={() => setPage(1)}>1</button>
                  {pageNumbers[0] > 2 && <span style={{ color: 'var(--color-gray-400)' }}>…</span>}
                </>
              )}
              {pageNumbers.map((p) => (
                <button
                  key={p}
                  className={`bl-page__page-btn ${p === page ? 'bl-page__page-btn--active' : ''}`}
                  onClick={() => setPage(p)}
                >
                  {p}
                </button>
              ))}
              {pageNumbers[pageNumbers.length - 1] < totalPages && (
                <>
                  {pageNumbers[pageNumbers.length - 1] < totalPages - 1 && <span style={{ color: 'var(--color-gray-400)' }}>…</span>}
                  <button className="bl-page__page-btn" onClick={() => setPage(totalPages)}>{totalPages}</button>
                </>
              )}
              <button
                className="bl-page__page-btn"
                disabled={page === totalPages}
                onClick={() => setPage(page + 1)}
                aria-label="Next page"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><polyline points="9 18 15 12 9 6" /></svg>
              </button>
            </nav>
          )}
        </div>
      </div>
    </div>
  );
}
