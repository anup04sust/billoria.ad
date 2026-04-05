import Link from 'next/link';
import type { Billboard } from '@/types/billboard';
import { IconImagePlaceholder } from '@/lib/icons/ui-icons';
import './billboard-list-card.css';

function formatPrice(price?: string, currency?: string) {
  if (!price) return null;
  const num = Number(price);
  if (isNaN(num)) return price;
  return `${currency === 'USD' ? '$' : '৳'}${num.toLocaleString('en-BD')}`;
}

export function BillboardListCard({ billboard: b }: { billboard: Billboard }) {
  const dimensions = b.width_ft && b.height_ft ? `${b.width_ft}' × ${b.height_ft}'` : b.display_size;

  return (
    <Link href={`/billboard/${b.uuid}`} className="bl-card">
      {/* Image */}
      <div className="bl-card__img-wrap">
        {b.hero_image?.medium ? (
          <img src={b.hero_image.medium} alt={b.hero_image.alt || b.title} className="bl-card__img" />
        ) : (
          <div className="bl-card__img-placeholder">
            <IconImagePlaceholder />
          </div>
        )}
        {b.is_premium === '1' && <span className="bl-card__premium-badge">Premium</span>}
        {b.availability_status?.label && (
          <span className={`bl-card__status bl-card__status--${b.availability_status.label.toLowerCase()}`}>
            {b.availability_status.label}
          </span>
        )}
      </div>

      {/* Body */}
      <div className="bl-card__body">
        <h3 className="bl-card__title">{b.title}</h3>
        <p className="bl-card__location">
          {[b.area_zone?.label, b.district?.label, b.division?.label].filter(Boolean).join(', ')}
        </p>

        <div className="bl-card__meta">
          {b.media_format?.label && (
            <span className="bl-card__tag">{b.media_format.label}</span>
          )}
          {b.placement_type?.label && (
            <span className="bl-card__tag">{b.placement_type.label}</span>
          )}
          {dimensions && (
            <span className="bl-card__tag">{dimensions}</span>
          )}
        </div>

        <div className="bl-card__footer">
          {b.rate_card_price ? (
            <span className="bl-card__price">{formatPrice(b.rate_card_price, b.currency)}</span>
          ) : (
            <span className="bl-card__price bl-card__price--contact">Contact for pricing</span>
          )}
          {b.facing_direction && (
            <span className="bl-card__facing">{b.facing_direction}</span>
          )}
        </div>
      </div>
    </Link>
  );
}
