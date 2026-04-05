'use client';

import { useState, useMemo, useEffect, useRef, useCallback } from 'react';
import type { Billboard } from '@/types/billboard';
import { billboardAPI } from '@/lib/api/billboard';
import { IconSearch, IconBillboard, IconLocationPin, IconGlobe, IconChevronRight } from '@/lib/icons/ui-icons';
import './search-overlay.css';

interface SearchOverlayProps {
  open: boolean;
  onClose: () => void;
}

interface SearchResult {
  type: 'billboard' | 'location' | 'zone';
  label: string;
  sub?: string;
  href: string;
  billboard?: Billboard;
}

const POPULAR_AREAS = [
  'Gulshan', 'Banani', 'Dhanmondi', 'Uttara', 'Motijheel',
  'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna',
];

export function SearchOverlay({ open, onClose }: SearchOverlayProps) {
  const [query, setQuery] = useState('');
  const [billboards, setBillboards] = useState<Billboard[]>([]);
  const [loaded, setLoaded] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  // Load billboard data once when overlay opens
  useEffect(() => {
    if (open && !loaded) {
      billboardAPI.listForMap().then((data) => {
        setBillboards(data);
        setLoaded(true);
      }).catch(() => setLoaded(true));
    }
    if (open) {
      setTimeout(() => {
        inputRef.current?.focus();
        setQuery('');
      }, 100);
    }
  }, [open, loaded]);

  // Close on Escape
  useEffect(() => {
    if (!open) return;
    const handleKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
    };
    document.addEventListener('keydown', handleKey);
    return () => document.removeEventListener('keydown', handleKey);
  }, [open, onClose]);

  // Search results
  const results = useMemo<SearchResult[]>(() => {
    const q = query.trim().toLowerCase();
    if (q.length < 2) return [];

    const seen = new Set<string>();
    const items: SearchResult[] = [];

    // Billboard matches
    for (const b of billboards) {
      if (items.length >= 12) break;
      const title = b.title?.toLowerCase() || '';
      const bid = b.billboard_id?.toLowerCase() || '';
      if (title.includes(q) || bid.includes(q)) {
        items.push({
          type: 'billboard',
          label: b.title,
          sub: b.area_zone?.label || b.district?.label,
          href: `/billboard/${b.uuid}`,
          billboard: b,
        });
      }
    }

    // Location matches (division/district)
    for (const b of billboards) {
      const div = b.division?.label || '';
      const dist = b.district?.label || '';
      if (div.toLowerCase().includes(q) && !seen.has(`div-${div}`)) {
        seen.add(`div-${div}`);
        items.push({ type: 'location', label: div, sub: 'Division', href: `/billboards?division=${encodeURIComponent(div)}` });
      }
      if (dist.toLowerCase().includes(q) && !seen.has(`dist-${dist}`)) {
        seen.add(`dist-${dist}`);
        items.push({ type: 'location', label: dist, sub: div || 'District', href: `/billboards?district=${encodeURIComponent(dist)}` });
      }
      if (items.length >= 20) break;
    }

    // Zone matches
    for (const b of billboards) {
      const zone = b.area_zone?.label || '';
      if (zone.toLowerCase().includes(q) && !seen.has(`zone-${zone}`)) {
        seen.add(`zone-${zone}`);
        items.push({ type: 'zone', label: zone, sub: b.district?.label, href: `/billboards?zone=${encodeURIComponent(zone)}` });
      }
      if (items.length >= 24) break;
    }

    return items;
  }, [query, billboards]);

  const grouped = useMemo(() => {
    const groups: Record<string, SearchResult[]> = {};
    for (const r of results) {
      const key = r.type === 'billboard' ? 'Billboards' : r.type === 'location' ? 'Locations' : 'Zones';
      if (!groups[key]) groups[key] = [];
      groups[key].push(r);
    }
    return groups;
  }, [results]);

  const handleOverlayClick = useCallback((e: React.MouseEvent) => {
    if ((e.target as HTMLElement).classList.contains('search-overlay')) {
      onClose();
    }
  }, [onClose]);

  if (!open) return null;

  return (
    <div className="search-overlay" onClick={handleOverlayClick}>
      <div className="search-overlay__modal">
        <div className="search-overlay__input-row">
          <span className="search-overlay__input-icon"><IconSearch /></span>
          <input
            ref={inputRef}
            type="text"
            className="search-overlay__input"
            placeholder="Search billboards, locations, zones..."
            value={query}
            onChange={(e) => setQuery(e.target.value)}
          />
          <button className="search-overlay__close" onClick={onClose} aria-label="Close search">
            <kbd>ESC</kbd>
          </button>
        </div>

        <div className="search-overlay__body">
          {query.length < 2 ? (
            <div className="search-overlay__suggestions">
              <h4 className="search-overlay__section-title">Popular Areas</h4>
              <div className="search-overlay__tags">
                {POPULAR_AREAS.map((area) => (
                  <button
                    key={area}
                    className="search-overlay__tag"
                    onClick={() => setQuery(area)}
                  >
                    {area}
                  </button>
                ))}
              </div>
            </div>
          ) : results.length === 0 ? (
            <div className="search-overlay__empty">
              <p>No results for &ldquo;{query}&rdquo;</p>
            </div>
          ) : (
            Object.entries(grouped).map(([group, items]) => (
              <div key={group} className="search-overlay__group">
                <h4 className="search-overlay__section-title">{group}</h4>
                {items.map((item, i) => (
                  <a key={`${item.type}-${i}`} href={item.href} className="search-overlay__result" onClick={onClose}>
                    <span className="search-overlay__result-icon">
                      {item.type === 'billboard' ? (
                        <IconBillboard />
                      ) : item.type === 'location' ? (
                        <IconLocationPin />
                      ) : (
                        <IconGlobe />
                      )}
                    </span>
                    <span className="search-overlay__result-info">
                      <span className="search-overlay__result-label">{item.label}</span>
                      {item.sub && <span className="search-overlay__result-sub">{item.sub}</span>}
                    </span>
                    <span className="search-overlay__result-arrow"><IconChevronRight /></span>
                  </a>
                ))}
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
}
