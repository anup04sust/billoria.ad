'use client';

import { useState, useMemo, useCallback } from 'react';
import type { Billboard } from '@/types/billboard';
import { IconX, IconImagePlaceholder, IconChevronLeft, IconChevronRight } from '@/lib/icons/ui-icons';
import './billboard-list-widget.css';

const PAGE_SIZE = 5;

interface BillboardListWidgetProps {
  billboards: Billboard[];
  onClose: () => void;
  onBillboardClick?: (billboard: Billboard) => void;
}

export function BillboardListWidget({ billboards, onClose, onBillboardClick }: BillboardListWidgetProps) {
  const [closing, setClosing] = useState(false);

  const handleClose = useCallback(() => {
    setClosing(true);
    setTimeout(onClose, 280);
  }, [onClose]);
  const [page, setPage] = useState(0);

  const totalPages = Math.max(1, Math.ceil(billboards.length / PAGE_SIZE));
  const paginated = useMemo(
    () => billboards.slice(page * PAGE_SIZE, (page + 1) * PAGE_SIZE),
    [billboards, page]
  );

  return (
    <div className={`list-widget ${closing ? 'list-widget--closing' : ''}`}>
      <div className="list-widget__header">
        <h3 className="list-widget__title">
          Billboards <span className="list-widget__count">({billboards.length})</span>
        </h3>
        <button className="list-widget__close" onClick={handleClose} aria-label="Close list view">
          <IconX />
        </button>
      </div>

      <div className="list-widget__body">
        {billboards.length === 0 ? (
          <p className="list-widget__empty">No billboards found.</p>
        ) : (
          paginated.map((b) => (
            <div
              key={b.id}
              className="list-widget__item"
              onClick={() => onBillboardClick?.(b)}
              role="button"
              tabIndex={0}
              onKeyDown={(e) => { if (e.key === 'Enter') onBillboardClick?.(b); }}
            >
              {b.hero_image ? (
                <img
                  src={b.hero_image.thumbnail}
                  alt={b.hero_image.alt || b.title}
                  className="list-widget__thumb"
                />
              ) : (
                <div className="list-widget__thumb list-widget__thumb--placeholder">
                  <IconImagePlaceholder />
                </div>
              )}
              <div className="list-widget__info">
                <span className="list-widget__name">{b.title}</span>
                {b.area_zone && (
                  <span className="list-widget__location">{b.area_zone.label}</span>
                )}
                <span className="list-widget__meta">
                  {b.media_format && <span>{b.media_format.label}</span>}
                  {b.rate_card_price && (
                    <span className="list-widget__price">
                      BDT {parseFloat(b.rate_card_price).toLocaleString()}
                    </span>
                  )}
                </span>
              </div>
              {b.availability_status && (
                <span
                  className={`list-widget__status list-widget__status--${b.availability_status.label.toLowerCase().replace(/\s+/g, '-')}`}
                >
                  {b.availability_status.label}
                </span>
              )}
            </div>
          ))
        )}
      </div>

      {totalPages > 1 && (
        <div className="list-widget__pager">
          <button
            className="list-widget__pager-btn"
            disabled={page === 0}
            onClick={() => setPage((p) => p - 1)}
            aria-label="Previous page"
          >
            <IconChevronLeft />
          </button>
          <span className="list-widget__pager-info">
            {page + 1} / {totalPages}
          </span>
          <button
            className="list-widget__pager-btn"
            disabled={page >= totalPages - 1}
            onClick={() => setPage((p) => p + 1)}
            aria-label="Next page"
          >
            <IconChevronRight />
          </button>
        </div>
      )}
    </div>
  );
}
