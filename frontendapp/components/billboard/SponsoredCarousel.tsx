'use client';

import { useState, useEffect, useCallback } from 'react';
import Link from 'next/link';
import type { Billboard } from '@/types/billboard';
import './sponsored-carousel.css';

function formatPrice(price?: string, currency?: string) {
  if (!price) return null;
  const num = Number(price);
  if (isNaN(num)) return price;
  return `${currency === 'USD' ? '$' : '৳'}${num.toLocaleString('en-BD')}`;
}

interface SponsoredCarouselProps {
  billboards: Billboard[];
}

export function SponsoredCarousel({ billboards }: SponsoredCarouselProps) {
  const [active, setActive] = useState(0);

  const next = useCallback(() => {
    setActive((i) => (i === billboards.length - 1 ? 0 : i + 1));
  }, [billboards.length]);

  const prev = useCallback(() => {
    setActive((i) => (i === 0 ? billboards.length - 1 : i - 1));
  }, [billboards.length]);

  // Auto-advance every 5s
  useEffect(() => {
    if (billboards.length <= 1) return;
    const timer = setInterval(next, 5000);
    return () => clearInterval(timer);
  }, [next, billboards.length]);

  if (billboards.length === 0) return null;

  const b = billboards[active];

  return (
    <div className="bl-sponsored">
      <div className="bl-sponsored__label">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </svg>
        Sponsored
      </div>
      <Link href={`/billboard/${b.uuid}`} className="bl-sponsored__card">
        <div className="bl-sponsored__img-wrap">
          {b.hero_image?.large ? (
            <img src={b.hero_image.large} alt={b.hero_image.alt || b.title} className="bl-sponsored__img" />
          ) : (
            <div className="bl-sponsored__img-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <circle cx="8.5" cy="8.5" r="1.5" />
                <path d="M21 15l-5-5L5 21" />
              </svg>
            </div>
          )}
          {b.availability_status?.label && (
            <span className={`bl-sponsored__status bl-sponsored__status--${b.availability_status.label.toLowerCase()}`}>
              {b.availability_status.label}
            </span>
          )}
        </div>
        <div className="bl-sponsored__body">
          <h3 className="bl-sponsored__title">{b.title}</h3>
          <p className="bl-sponsored__location">
            {[b.area_zone?.label, b.district?.label, b.division?.label].filter(Boolean).join(', ')}
          </p>
          <div className="bl-sponsored__meta">
            {b.media_format?.label && <span className="bl-sponsored__tag">{b.media_format.label}</span>}
            {b.placement_type?.label && <span className="bl-sponsored__tag">{b.placement_type.label}</span>}
            {b.width_ft && b.height_ft && <span className="bl-sponsored__tag">{b.width_ft}&apos; × {b.height_ft}&apos;</span>}
          </div>
          {b.rate_card_price && (
            <span className="bl-sponsored__price">{formatPrice(b.rate_card_price, b.currency)}</span>
          )}
        </div>
      </Link>

      {/* Nav arrows */}
      {billboards.length > 1 && (
        <>
          <button className="bl-sponsored__nav bl-sponsored__nav--prev" onClick={(e) => { e.preventDefault(); prev(); }} aria-label="Previous">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><polyline points="15 18 9 12 15 6" /></svg>
          </button>
          <button className="bl-sponsored__nav bl-sponsored__nav--next" onClick={(e) => { e.preventDefault(); next(); }} aria-label="Next">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><polyline points="9 18 15 12 9 6" /></svg>
          </button>
          {/* Dots */}
          <div className="bl-sponsored__dots">
            {billboards.map((_, i) => (
              <button
                key={i}
                className={`bl-sponsored__dot ${i === active ? 'bl-sponsored__dot--active' : ''}`}
                onClick={(e) => { e.preventDefault(); setActive(i); }}
                aria-label={`Go to slide ${i + 1}`}
              />
            ))}
          </div>
        </>
      )}
    </div>
  );
}
