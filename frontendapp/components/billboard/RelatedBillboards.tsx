import Link from 'next/link';
import type { Billboard } from '@/types/billboard';
import './billboard-related.css';

interface RelatedBillboardsProps {
  title: string;
  billboards: Billboard[];
}

function formatPrice(price?: string, currency?: string) {
  if (!price) return null;
  const num = Number(price);
  if (isNaN(num)) return price;
  return `${currency === 'USD' ? '$' : '৳'}${num.toLocaleString('en-BD')}`;
}

export function RelatedBillboards({ title, billboards }: RelatedBillboardsProps) {
  if (billboards.length === 0) return null;

  return (
    <section className="bb-related">
      <h2 className="bb-related__title">{title}</h2>
      <div className="bb-related__grid">
        {billboards.map((b) => (
          <Link key={b.id} href={`/billboard/${b.uuid}`} className="bb-related__card">
            <div className="bb-related__img-wrap">
              {b.hero_image?.medium ? (
                <img
                  src={b.hero_image.medium}
                  alt={b.hero_image.alt || b.title}
                  className="bb-related__img"
                />
              ) : (
                <div className="bb-related__img-placeholder">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                    <path d="M21 15l-5-5L5 21" />
                  </svg>
                </div>
              )}
              {b.availability_status?.label && (
                <span className={`bb-related__status bb-related__status--${b.availability_status.label.toLowerCase()}`}>
                  {b.availability_status.label}
                </span>
              )}
            </div>
            <div className="bb-related__body">
              <h3 className="bb-related__name">{b.title}</h3>
              <span className="bb-related__location">
                {[b.area_zone?.label, b.district?.label].filter(Boolean).join(', ')}
              </span>
              {b.media_format?.label && (
                <span className="bb-related__format">{b.media_format.label}</span>
              )}
              {b.rate_card_price && (
                <span className="bb-related__price">
                  {formatPrice(b.rate_card_price, b.currency)}
                </span>
              )}
            </div>
          </Link>
        ))}
      </div>
    </section>
  );
}
