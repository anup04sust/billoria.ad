'use client';

import { useState, useMemo } from 'react';
import type { Billboard } from '@/types/billboard';
import './billboard-list-filters.css';

export const TIER_AREAS: Record<string, string[]> = {
  'Tier 1 — Premium': ['Gulshan', 'Banani', 'Dhanmondi', 'Uttara', 'Motijheel', 'Mirpur'],
  'Tier 2 — Urban': ['Farmgate', 'Karwan Bazar', 'Mohakhali', 'Tejgaon', 'Badda', 'Rampura', 'Malibagh', 'Shantinagar', 'Nilkhet'],
  'Tier 3 — Suburban': ['Tongi', 'Gazipur Chowrasta', 'Joydevpur', 'Bashabo', 'Siddhirganj', 'Narayanganj'],
  'City — Chittagong': ['GEC Circle', 'Agrabad', 'Nasirabad', 'Muradpur', 'Oxygen', 'Chawkbazar'],
  'City — Sylhet': ['Zindabazar', 'Ambarkhana', 'Chowhatta', 'Airport Road'],
  'City — Khulna': ['KDA Avenue', 'Boyra', 'Shonadanga'],
  'City — Rajshahi': ['Saheb Bazar', 'Uposhohor', 'Kazla'],
};

export interface ListFilters {
  search: string;
  divisions: string[];
  districts: string[];
  mediaFormats: string[];
  placementTypes: string[];
  roadTypes: string[];
  statuses: string[];
  tiers: string[];
  priceRange: [number | null, number | null];
  premiumOnly: boolean;
}

export const EMPTY_LIST_FILTERS: ListFilters = {
  search: '',
  divisions: [],
  districts: [],
  mediaFormats: [],
  placementTypes: [],
  roadTypes: [],
  statuses: [],
  tiers: [],
  priceRange: [null, null],
  premiumOnly: false,
};

function uniqueLabels(billboards: Billboard[], field: (b: Billboard) => string | undefined): string[] {
  const set = new Set<string>();
  for (const b of billboards) {
    const v = field(b);
    if (v) set.add(v);
  }
  return [...set].sort();
}

interface BillboardListFiltersProps {
  billboards: Billboard[];
  filters: ListFilters;
  onChange: (filters: ListFilters) => void;
  resultCount: number;
}

function FilterGroup({ title, children, defaultOpen = true }: { title: string; children: React.ReactNode; defaultOpen?: boolean }) {
  const [open, setOpen] = useState(defaultOpen);
  return (
    <div className="bl-filters__group">
      <button className="bl-filters__group-toggle" onClick={() => setOpen(!open)} type="button">
        <span>{title}</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" className={open ? 'bl-filters__chevron--open' : ''}>
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </button>
      {open && <div className="bl-filters__group-body">{children}</div>}
    </div>
  );
}

function CheckboxList({
  options,
  selected,
  onToggle,
  max = 8,
}: {
  options: string[];
  selected: string[];
  onToggle: (val: string) => void;
  max?: number;
}) {
  const [showAll, setShowAll] = useState(false);
  const visible = showAll ? options : options.slice(0, max);
  return (
    <>
      {visible.map((opt) => (
        <label key={opt} className="bl-filters__checkbox">
          <input
            type="checkbox"
            checked={selected.includes(opt)}
            onChange={() => onToggle(opt)}
          />
          <span>{opt}</span>
        </label>
      ))}
      {options.length > max && (
        <button className="bl-filters__show-more" onClick={() => setShowAll(!showAll)} type="button">
          {showAll ? 'Show less' : `Show all ${options.length}`}
        </button>
      )}
    </>
  );
}

export function BillboardListFilters({ billboards, filters, onChange, resultCount }: BillboardListFiltersProps) {
  const divisions = useMemo(() => uniqueLabels(billboards, (b) => b.division?.label), [billboards]);
  const districts = useMemo(() => {
    if (filters.divisions.length === 0) return uniqueLabels(billboards, (b) => b.district?.label);
    return uniqueLabels(
      billboards.filter((b) => filters.divisions.includes(b.division?.label || '')),
      (b) => b.district?.label
    );
  }, [billboards, filters.divisions]);
  const mediaFormats = useMemo(() => uniqueLabels(billboards, (b) => b.media_format?.label), [billboards]);
  const placementTypes = useMemo(() => uniqueLabels(billboards, (b) => b.placement_type?.label), [billboards]);
  const roadTypes = useMemo(() => uniqueLabels(billboards, (b) => b.road_type?.label), [billboards]);
  const statuses = useMemo(() => uniqueLabels(billboards, (b) => b.availability_status?.label), [billboards]);

  const toggle = (field: keyof ListFilters, value: string) => {
    const arr = filters[field] as string[];
    onChange({
      ...filters,
      [field]: arr.includes(value) ? arr.filter((v) => v !== value) : [...arr, value],
    });
  };

  const clearAll = () => onChange({ ...EMPTY_LIST_FILTERS });

  const hasFilters = filters.search ||
    filters.divisions.length > 0 ||
    filters.districts.length > 0 ||
    filters.mediaFormats.length > 0 ||
    filters.placementTypes.length > 0 ||
    filters.roadTypes.length > 0 ||
    filters.statuses.length > 0 ||
    filters.tiers.length > 0 ||
    filters.premiumOnly ||
    filters.priceRange[0] !== null ||
    filters.priceRange[1] !== null;

  return (
    <aside className="bl-filters">
      <div className="bl-filters__header">
        <h2 className="bl-filters__title">Filters</h2>
        {hasFilters && (
          <button className="bl-filters__clear" onClick={clearAll} type="button">
            Clear all
          </button>
        )}
      </div>

      <div className="bl-filters__count">{resultCount} billboard{resultCount !== 1 ? 's' : ''} found</div>

      {/* Search */}
      <div className="bl-filters__search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
          <circle cx="11" cy="11" r="8" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input
          type="text"
          placeholder="Search billboards..."
          value={filters.search}
          onChange={(e) => onChange({ ...filters, search: e.target.value })}
        />
        {filters.search && (
          <button className="bl-filters__search-clear" onClick={() => onChange({ ...filters, search: '' })} type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
              <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        )}
      </div>

      <div className="bl-filters__body">
        {/* Tier */}
        <FilterGroup title="Area Tier">
          {Object.keys(TIER_AREAS).map((tier) => (
            <label key={tier} className="bl-filters__checkbox">
              <input
                type="checkbox"
                checked={filters.tiers.includes(tier)}
                onChange={() => toggle('tiers', tier)}
              />
              <span>{tier}</span>
            </label>
          ))}
        </FilterGroup>

        {/* Division */}
        <FilterGroup title="Division">
          <CheckboxList options={divisions} selected={filters.divisions} onToggle={(v) => toggle('divisions', v)} />
        </FilterGroup>

        {/* District */}
        <FilterGroup title="District" defaultOpen={false}>
          <CheckboxList options={districts} selected={filters.districts} onToggle={(v) => toggle('districts', v)} />
        </FilterGroup>

        {/* Media Format */}
        <FilterGroup title="Media Format">
          <CheckboxList options={mediaFormats} selected={filters.mediaFormats} onToggle={(v) => toggle('mediaFormats', v)} />
        </FilterGroup>

        {/* Placement Type */}
        <FilterGroup title="Placement Type" defaultOpen={false}>
          <CheckboxList options={placementTypes} selected={filters.placementTypes} onToggle={(v) => toggle('placementTypes', v)} />
        </FilterGroup>

        {/* Road Type */}
        <FilterGroup title="Road Type" defaultOpen={false}>
          <CheckboxList options={roadTypes} selected={filters.roadTypes} onToggle={(v) => toggle('roadTypes', v)} />
        </FilterGroup>

        {/* Status */}
        <FilterGroup title="Availability">
          <CheckboxList options={statuses} selected={filters.statuses} onToggle={(v) => toggle('statuses', v)} />
        </FilterGroup>

        {/* Price Range */}
        <FilterGroup title="Price Range" defaultOpen={false}>
          <div className="bl-filters__price-row">
            <input
              type="number"
              placeholder="Min"
              value={filters.priceRange[0] ?? ''}
              onChange={(e) =>
                onChange({
                  ...filters,
                  priceRange: [e.target.value ? Number(e.target.value) : null, filters.priceRange[1]],
                })
              }
            />
            <span>—</span>
            <input
              type="number"
              placeholder="Max"
              value={filters.priceRange[1] ?? ''}
              onChange={(e) =>
                onChange({
                  ...filters,
                  priceRange: [filters.priceRange[0], e.target.value ? Number(e.target.value) : null],
                })
              }
            />
          </div>
        </FilterGroup>

        {/* Premium */}
        <label className="bl-filters__checkbox bl-filters__premium">
          <input
            type="checkbox"
            checked={filters.premiumOnly}
            onChange={() => onChange({ ...filters, premiumOnly: !filters.premiumOnly })}
          />
          <span>Premium only</span>
        </label>
      </div>
    </aside>
  );
}
