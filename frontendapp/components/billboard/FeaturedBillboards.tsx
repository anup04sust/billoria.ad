import Link from 'next/link';
import type { Billboard } from '@/types/billboard';
import { IconLightning, IconImagePlaceholder } from '@/lib/icons/ui-icons';
import './featured-billboards.css';

function formatPrice(price?: string, currency?: string) {
  if (!price) return null;
  const num = Number(price);
  if (isNaN(num)) return price;
  return `${currency === 'USD' ? '$' : '৳'}${num.toLocaleString('en-BD')}`;
}

interface FeaturedBillboardsProps {
  billboards: Billboard[];
}

export function FeaturedBillboards({ billboards }: FeaturedBillboardsProps) {
  if (billboards.length === 0) return null;

  return (
    <div className="bl-featured">
      <div className="bl-featured__label">
        <IconLightning />
        Featured
      </div>
      <div className="bl-featured__grid">
        {billboards.slice(0, 2).map((b) => (
          <Link key={b.id} href={`/billboard/${b.uuid}`} className="bl-featured__card">
            <div className="bl-featured__img-wrap">
              {b.hero_image?.medium ? (
                <img src={b.hero_image.medium} alt={b.hero_image.alt || b.title} className="bl-featured__img" />
              ) : (
                <div className="bl-featured__img-placeholder">
                  <IconImagePlaceholder />
                </div>
              )}
              {b.availability_status?.label && (
                <span className={`bl-featured__status bl-featured__status--${b.availability_status.label.toLowerCase()}`}>
                  {b.availability_status.label}
                </span>
              )}
              {b.is_premium === '1' && <span className="bl-featured__premium">Premium</span>}
            </div>
            <div className="bl-featured__body">
              <h3 className="bl-featured__title">{b.title}</h3>
              <p className="bl-featured__location">
                {[b.area_zone?.label, b.district?.label].filter(Boolean).join(', ')}
              </p>
              <div className="bl-featured__meta">
                {b.media_format?.label && <span className="bl-featured__tag">{b.media_format.label}</span>}
                {b.width_ft && b.height_ft && <span className="bl-featured__tag">{b.width_ft}&apos; × {b.height_ft}&apos;</span>}
              </div>
              <div className="bl-featured__footer">
                {b.rate_card_price ? (
                  <span className="bl-featured__price">{formatPrice(b.rate_card_price, b.currency)}</span>
                ) : (
                  <span className="bl-featured__price bl-featured__price--contact">Contact for pricing</span>
                )}
              </div>
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
}
