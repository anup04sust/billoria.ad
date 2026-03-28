'use client';

import { useState, useCallback } from 'react';
import type { Billboard } from '@/types/billboard';
import './billboard-detail-widget.css';

interface BillboardDetailWidgetProps {
  billboard: Billboard;
  onClose: () => void;
}

export function BillboardDetailWidget({ billboard, onClose }: BillboardDetailWidgetProps) {
  const [closing, setClosing] = useState(false);

  const handleClose = useCallback(() => {
    setClosing(true);
    setTimeout(onClose, 280);
  }, [onClose]);

  return (
    <div className={`detail-widget ${closing ? 'detail-widget--closing' : ''}`} key={billboard.id}>
      <div className="detail-widget__header">
        <h3 className="detail-widget__title">Billboard Details</h3>
        <button className="detail-widget__close" onClick={handleClose} aria-label="Close details">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>

      <div className="detail-widget__body">
        {billboard.hero_image && (
          <div className="detail-widget__image">
            <img
              src={billboard.hero_image.large || billboard.hero_image.medium}
              alt={billboard.hero_image.alt || billboard.title}
              className="detail-widget__img"
            />
          </div>
        )}

        <div className="detail-widget__content">
          <h4 className="detail-widget__name">{billboard.title}</h4>

          {billboard.billboard_id && (
            <span className="detail-widget__id">ID: {billboard.billboard_id}</span>
          )}

          {billboard.availability_status && (
            <span
              className={`detail-widget__status detail-widget__status--${billboard.availability_status.label.toLowerCase().replace(/\s+/g, '-')}`}
            >
              {billboard.availability_status.label}
            </span>
          )}

          {billboard.rate_card_price && (
            <div className="detail-widget__price">
              BDT {parseFloat(billboard.rate_card_price).toLocaleString()}
              {billboard.currency && <span className="detail-widget__currency"> / {billboard.currency}</span>}
            </div>
          )}

          <div className="detail-widget__details">
            {billboard.area_zone && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Location</span>
                <span className="detail-widget__value">{billboard.area_zone.label}</span>
              </div>
            )}
            {billboard.division && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Division</span>
                <span className="detail-widget__value">{billboard.division.label}</span>
              </div>
            )}
            {billboard.district && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">District</span>
                <span className="detail-widget__value">{billboard.district.label}</span>
              </div>
            )}
            {billboard.media_format && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Format</span>
                <span className="detail-widget__value">{billboard.media_format.label}</span>
              </div>
            )}
            {billboard.placement_type && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Placement</span>
                <span className="detail-widget__value">{billboard.placement_type.label}</span>
              </div>
            )}
            {(billboard.width_ft || billboard.height_ft) && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Size</span>
                <span className="detail-widget__value">
                  {billboard.width_ft && billboard.height_ft
                    ? `${billboard.width_ft}' × ${billboard.height_ft}'`
                    : billboard.display_size}
                </span>
              </div>
            )}
            {billboard.road_name && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Road</span>
                <span className="detail-widget__value">{billboard.road_name.label}</span>
              </div>
            )}
            {billboard.road_type && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Road Type</span>
                <span className="detail-widget__value">{billboard.road_type.label}</span>
              </div>
            )}
            {billboard.facing_direction && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Facing</span>
                <span className="detail-widget__value">{billboard.facing_direction}</span>
              </div>
            )}
            {billboard.owner_organization && (
              <div className="detail-widget__row">
                <span className="detail-widget__label">Owner</span>
                <span className="detail-widget__value">{billboard.owner_organization.label}</span>
              </div>
            )}
          </div>
        </div>
      </div>

      <div className="detail-widget__footer">
        <a href={`/billboard/${billboard.id}`} className="detail-widget__cta">
          View Full Details →
        </a>
      </div>
    </div>
  );
}
