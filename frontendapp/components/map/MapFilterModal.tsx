'use client';

import { useState, useMemo, useEffect, useRef } from 'react';
import type { Billboard } from '@/types/billboard';
import { getTaxonomyIcon } from '@/lib/icons/billboard-icons';
import { IconX } from '@/lib/icons/ui-icons';
import './map-filter-modal.css';

/** Extract sorted unique labels from a billboard taxonomy field. */
function uniqueLabels(billboards: Billboard[], field: (b: Billboard) => string | undefined): string[] {
  const set = new Set<string>();
  for (const b of billboards) {
    const v = field(b);
    if (v) set.add(v);
  }
  return [...set].sort();
}

/* ── Types ── */
export interface MapFilters {
  search: string;
  divisions: string[];
  districts: string[];
  roadTypes: string[];
  roadNames: string[];
  mediaFormats: string[];
  placementTypes: string[];
  statuses: string[];
}

export const EMPTY_FILTERS: MapFilters = {
  search: '',
  divisions: [],
  districts: [],
  roadTypes: [],
  roadNames: [],
  mediaFormats: [],
  placementTypes: [],
  statuses: [],
};

type Tab = 'search' | 'locations' | 'types' | 'status';

interface MapFilterModalProps {
  open: boolean;
  filters: MapFilters;
  billboards: Billboard[];
  onClose: () => void;
  onApply: (filters: MapFilters) => void;
}

export function MapFilterModal({ open, filters, billboards, onClose, onApply }: MapFilterModalProps) {
  const [tab, setTab] = useState<Tab>('search');
  const [draft, setDraft] = useState<MapFilters>(filters);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const searchRef = useRef<HTMLDivElement>(null);

  /* Reset draft when modal opens */
  useEffect(() => {
    if (open) {
      setDraft(filters);
      setShowSuggestions(false);
    }
  }, [open, filters]);

  /* Close suggestions on outside click */
  useEffect(() => {
    const handleClick = (e: MouseEvent) => {
      if (searchRef.current && !searchRef.current.contains(e.target as Node)) {
        setShowSuggestions(false);
      }
    };
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  /* Typeahead suggestions (max 8) */
  const suggestions = useMemo(() => {
    const q = draft.search.trim().toLowerCase();
    if (q.length < 2) return [];
    return billboards
      .filter((b) => {
        const title = b.title?.toLowerCase() || '';
        const bid = b.billboard_id?.toLowerCase() || '';
        const zone = b.area_zone?.label?.toLowerCase() || '';
        return title.includes(q) || bid.includes(q) || zone.includes(q);
      })
      .slice(0, 8);
  }, [draft.search, billboards]);

  /* ── Helpers ── */
  const toggle = (field: keyof MapFilters, value: string) => {
    setDraft((prev) => {
      const arr = prev[field] as string[];
      return {
        ...prev,
        [field]: arr.includes(value) ? arr.filter((v) => v !== value) : [...arr, value],
      };
    });
  };

  const removeTag = (field: keyof MapFilters, value: string) => {
    setDraft((prev) => ({
      ...prev,
      [field]: (prev[field] as string[]).filter((v) => v !== value),
    }));
  };

  /* Collect all selected tags for the tag bar */
  const selectedTags = useMemo(() => {
    const tags: { field: keyof MapFilters; value: string }[] = [];
    draft.divisions.forEach((v) => tags.push({ field: 'divisions', value: v }));
    draft.districts.forEach((v) => tags.push({ field: 'districts', value: v }));
    draft.roadTypes.forEach((v) => tags.push({ field: 'roadTypes', value: v }));
    draft.roadNames.forEach((v) => tags.push({ field: 'roadNames', value: v }));
    draft.mediaFormats.forEach((v) => tags.push({ field: 'mediaFormats', value: v }));
    draft.placementTypes.forEach((v) => tags.push({ field: 'placementTypes', value: v }));
    draft.statuses.forEach((v) => tags.push({ field: 'statuses', value: v }));
    return tags;
  }, [draft]);

  /* ── Derive filter options from billboard data ── */
  const divisions = useMemo(() => uniqueLabels(billboards, (b) => b.division?.label), [billboards]);
  const allDistricts = useMemo(() => {
    // Build division → district[] map from actual data.
    const map = new Map<string, Set<string>>();
    for (const b of billboards) {
      if (b.division?.label && b.district?.label) {
        if (!map.has(b.division.label)) map.set(b.division.label, new Set());
        map.get(b.division.label)!.add(b.district.label);
      }
    }
    return map;
  }, [billboards]);
  const roadTypes = useMemo(() => uniqueLabels(billboards, (b) => b.road_type?.label), [billboards]);
  const roadNames = useMemo(() => uniqueLabels(billboards, (b) => b.road_name?.label), [billboards]);
  const mediaFormats = useMemo(() => uniqueLabels(billboards, (b) => b.media_format?.label), [billboards]);
  const placementTypes = useMemo(() => uniqueLabels(billboards, (b) => b.placement_type?.label), [billboards]);
  const statuses = useMemo(() => uniqueLabels(billboards, (b) => b.availability_status?.label), [billboards]);

  /* Available districts based on selected divisions */
  const availableDistricts = useMemo(() => {
    if (draft.divisions.length === 0) {
      const all = new Set<string>();
      allDistricts.forEach((set) => set.forEach((d) => all.add(d)));
      return [...all].sort();
    }
    const result = new Set<string>();
    draft.divisions.forEach((div) => {
      allDistricts.get(div)?.forEach((d) => result.add(d));
    });
    return [...result].sort();
  }, [draft.divisions, allDistricts]);

  const handleApply = () => {
    onApply(draft);
    onClose();
  };

  const handleClear = () => {
    const empty = { ...EMPTY_FILTERS };
    setDraft(empty);
    onApply(empty);
    onClose();
  };

  if (!open) return null;

  const TABS: { key: Tab; label: string }[] = [
    { key: 'search', label: 'Search' },
    { key: 'locations', label: 'Locations' },
    { key: 'types', label: 'Types' },
    { key: 'status', label: 'Status' },
  ];

  return (
    <div className="map-filter-overlay" onClick={onClose}>
      <div className="map-filter-modal" onClick={(e) => e.stopPropagation()}>
        {/* Header */}
        <div className="map-filter-modal__header">
          <h3>Filter Billboards</h3>
          <button className="map-filter-modal__close" onClick={onClose} aria-label="Close">
            <IconX />
          </button>
        </div>

        {/* Tabs */}
        <div className="map-filter-modal__tabs">
          {TABS.map((t) => (
            <button
              key={t.key}
              className={`map-filter-modal__tab ${tab === t.key ? 'map-filter-modal__tab--active' : ''}`}
              onClick={() => setTab(t.key)}
            >
              {t.label}
            </button>
          ))}
        </div>

        {/* Tab content */}
        <div className="map-filter-modal__body">
          {tab === 'search' && (
            <div className="map-filter-panel">
              <label className="map-filter-panel__label">Search by name or billboard ID</label>
              <div className="map-filter-search" ref={searchRef}>
                <input
                  type="text"
                  className="map-filter-panel__input"
                  placeholder="e.g. Gulshan, BD-00012"
                  value={draft.search}
                  onChange={(e) => {
                    setDraft((p) => ({ ...p, search: e.target.value }));
                    setShowSuggestions(true);
                  }}
                  onFocus={() => setShowSuggestions(true)}
                />
                {showSuggestions && suggestions.length > 0 && (
                  <ul className="map-filter-suggestions">
                    {suggestions.map((b) => (
                      <li key={b.id}>
                        <button
                          className="map-filter-suggestions__item"
                          onClick={() => {
                            setDraft((p) => ({ ...p, search: b.title }));
                            setShowSuggestions(false);
                          }}
                        >
                          {b.media_format?.label && (
                            <img
                              src={getTaxonomyIcon('media_format', b.media_format.label) || ''}
                              alt=""
                              className="map-filter-suggestions__icon"
                            />
                          )}
                          <span className="map-filter-suggestions__title">{b.title}</span>
                          {b.area_zone?.label && (
                            <span className="map-filter-suggestions__sub">{b.area_zone.label}</span>
                          )}
                          {b.billboard_id && (
                            <span className="map-filter-suggestions__id">{b.billboard_id}</span>
                          )}
                        </button>
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            </div>
          )}

          {tab === 'locations' && (
            <div className="map-filter-panel">
              <fieldset className="map-filter-panel__group">
                <legend className="map-filter-panel__label">Division</legend>
                <div className="map-filter-panel__chips">
                  {divisions.map((d) => (
                    <button
                      key={d}
                      className={`map-filter-chip ${draft.divisions.includes(d) ? 'map-filter-chip--selected' : ''}`}
                      onClick={() => toggle('divisions', d)}
                    >
                      {d}
                    </button>
                  ))}
                </div>
              </fieldset>

              <fieldset className="map-filter-panel__group">
                <legend className="map-filter-panel__label">District</legend>
                <div className="map-filter-panel__chips">
                  {availableDistricts.map((d) => (
                    <button
                      key={d}
                      className={`map-filter-chip ${draft.districts.includes(d) ? 'map-filter-chip--selected' : ''}`}
                      onClick={() => toggle('districts', d)}
                    >
                      {d}
                    </button>
                  ))}
                </div>
              </fieldset>

              <fieldset className="map-filter-panel__group">
                <legend className="map-filter-panel__label">Road Type</legend>
                <div className="map-filter-panel__chips">
                  {roadTypes.map((r) => (
                    <button
                      key={r}
                      className={`map-filter-chip ${draft.roadTypes.includes(r) ? 'map-filter-chip--selected' : ''}`}
                      onClick={() => toggle('roadTypes', r)}
                    >
                      {r}
                    </button>
                  ))}
                </div>
              </fieldset>

              <fieldset className="map-filter-panel__group">
                <legend className="map-filter-panel__label">Major Roads & Highways</legend>
                <div className="map-filter-panel__chips">
                  {roadNames.map((r) => (
                    <button
                      key={r}
                      className={`map-filter-chip ${draft.roadNames.includes(r) ? 'map-filter-chip--selected' : ''}`}
                      onClick={() => toggle('roadNames', r)}
                    >
                      {r}
                    </button>
                  ))}
                </div>
              </fieldset>
            </div>
          )}

          {tab === 'types' && (
            <div className="map-filter-panel">
              <fieldset className="map-filter-panel__group">
                <legend className="map-filter-panel__label">Media Format</legend>
                <div className="map-filter-panel__chips">
                  {mediaFormats.map((m) => (
                    <button
                      key={m}
                      className={`map-filter-chip ${draft.mediaFormats.includes(m) ? 'map-filter-chip--selected' : ''}`}
                      onClick={() => toggle('mediaFormats', m)}
                    >
                      {m}
                    </button>
                  ))}
                </div>
              </fieldset>

              <fieldset className="map-filter-panel__group">
                <legend className="map-filter-panel__label">Placement Type</legend>
                <div className="map-filter-panel__chips">
                  {placementTypes.map((p) => (
                    <button
                      key={p}
                      className={`map-filter-chip ${draft.placementTypes.includes(p) ? 'map-filter-chip--selected' : ''}`}
                      onClick={() => toggle('placementTypes', p)}
                    >
                      {p}
                    </button>
                  ))}
                </div>
              </fieldset>
            </div>
          )}

          {tab === 'status' && (
            <div className="map-filter-panel">
              <fieldset className="map-filter-panel__group">
                <legend className="map-filter-panel__label">Availability Status</legend>
                <div className="map-filter-panel__chips">
                  {statuses.map((s) => (
                    <button
                      key={s}
                      className={`map-filter-chip ${draft.statuses.includes(s) ? 'map-filter-chip--selected' : ''}`}
                      onClick={() => toggle('statuses', s)}
                    >
                      {s}
                    </button>
                  ))}
                </div>
              </fieldset>
            </div>
          )}
        </div>

        {/* Selected tags */}
        {selectedTags.length > 0 && (
          <div className="map-filter-modal__tags">
            {selectedTags.map(({ field, value }) => (
              <span key={`${field}-${value}`} className="map-filter-tag">
                {value}
                <button
                  className="map-filter-tag__remove"
                  onClick={() => removeTag(field, value)}
                  aria-label={`Remove ${value}`}
                >
                  <IconX />
                </button>
              </span>
            ))}
          </div>
        )}

        {/* Footer */}
        <div className="map-filter-modal__footer">
          <button className="map-filter-modal__clear" onClick={handleClear}>
            Clear All
          </button>
          <button className="map-filter-modal__apply" onClick={handleApply}>
            Filter Now
          </button>
        </div>
      </div>
    </div>
  );
}
