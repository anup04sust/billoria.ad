import Link from 'next/link';
import type { Billboard } from '@/types/billboard';
import { IconImagePlaceholder, IconLocationPin, IconBriefcase } from '@/lib/icons/ui-icons';
import './billboard-inventory.css';

interface BillboardInventoryCardProps {
  billboard: Billboard;
  basePath?: string;
}

const STATUS_COLORS: Record<string, string> = {
  available: 'bi-status--green',
  booked: 'bi-status--blue',
  maintenance: 'bi-status--amber',
  inactive: 'bi-status--gray',
};

const REVIEW_STATUS_COLORS: Record<string, string> = {
  draft: 'bi-status--gray',
  pending_review: 'bi-status--amber',
  approved: 'bi-status--green',
  revision_requested: 'bi-status--blue',
  rejected: 'bi-status--red',
};

const REVIEW_STATUS_LABELS: Record<string, string> = {
  draft: 'Draft',
  pending_review: 'Pending Review',
  approved: 'Approved',
  revision_requested: 'Revision Requested',
  rejected: 'Rejected',
};

export function BillboardInventoryCard({ billboard, basePath }: BillboardInventoryCardProps) {
  return (
    <Link 
      href={`${basePath ? basePath : '/billboards'}/${billboard.uuid}`}
      className="bi-card"
    >
      <div className="bi-card__image">
        {billboard.hero_image ? (
          <img 
            src={billboard.hero_image.medium} 
            alt={billboard.hero_image.alt || billboard.title}
          />
        ) : (
          <div className="bi-card__placeholder">
            <IconImagePlaceholder />
          </div>
        )}
        {billboard.is_premium === '1' && (
          <span className="bi-card__badge bi-card__badge--premium">Premium</span>
        )}
        <span className={`bi-card__status bi-status ${STATUS_COLORS[billboard.availability_status?.label?.toLowerCase() || 'inactive']}`}>
          {billboard.availability_status?.label || 'Unknown'}
        </span>
      </div>

      <div className="bi-card__content">
        <h3 className="bi-card__title">{billboard.title}</h3>
        <p className="bi-card__id">ID: {billboard.billboard_id || billboard.id}</p>
        
        <div className="bi-card__review-status">
          <span className={`bi-status ${REVIEW_STATUS_COLORS[billboard.review_status || 'draft']}`}>
            {REVIEW_STATUS_LABELS[billboard.review_status || 'draft'] || billboard.review_status}
          </span>
        </div>

        <div className="bi-card__meta">
          <div className="bi-card__meta-item">
            <IconLocationPin />
            <span>{billboard.area_zone?.label || billboard.district?.label || 'N/A'}</span>
          </div>
          
          <div className="bi-card__meta-item">
            <IconBriefcase />
            <span>{billboard.media_format?.label || 'N/A'}</span>
          </div>
        </div>

        <div className="bi-card__footer">
          <div className="bi-card__price">
            <span className="bi-card__price-label">Rate Card</span>
            <span className="bi-card__price-value">
              ৳{Number(billboard.rate_card_price || 0).toLocaleString()}
            </span>
          </div>
        </div>
      </div>
    </Link>
  );
}
