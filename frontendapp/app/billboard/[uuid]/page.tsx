import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { Breadcrumb } from '@/components/shared/Breadcrumb';
import { BillboardGallery } from '@/components/billboard/BillboardGallery';
import { BillboardLocationMap } from '@/components/billboard/BillboardLocationMap';
import { BillboardVideoSection } from '@/components/billboard/BillboardVideoSection';
import { RelatedBillboards } from '@/components/billboard/RelatedBillboards';
import { BookingCalendar } from '@/components/billboard/BookingCalendar';
import { Chatbot } from '@/components/shared/Chatbot';
import { billboardAPI } from '@/lib/api/billboard';
import type { Billboard } from '@/types/billboard';
import '@/components/billboard/billboard-detail.css';

interface PageProps {
  params: Promise<{ uuid: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { uuid } = await params;
  try {
    const res = await billboardAPI.getByUuid(uuid);
    const b = res.data;
    return {
      title: `${b.title} — Billoria`,
      description: `${b.media_format?.label || 'Billboard'} in ${b.district?.label || ''}, ${b.division?.label || ''}`,
    };
  } catch {
    return { title: 'Billboard — Billoria' };
  }
}

function formatPrice(price?: string, currency?: string) {
  if (!price) return null;
  const num = Number(price);
  if (isNaN(num)) return price;
  const formatted = num.toLocaleString('en-BD');
  return `${currency === 'USD' ? '$' : '৳'}${formatted}`;
}

function availabilityBadgeClass(label?: string) {
  if (!label) return 'bb-detail__badge--format';
  const l = label.toLowerCase();
  if (l === 'available') return 'bb-detail__badge--available';
  if (l === 'booked') return 'bb-detail__badge--booked';
  if (l === 'reserved') return 'bb-detail__badge--reserved';
  return 'bb-detail__badge--format';
}

function InfoItem({ label, value }: { label: string; value?: string | null }) {
  if (!value) return null;
  return (
    <div className="bb-detail__info-item">
      <span className="bb-detail__info-label">{label}</span>
      <span className="bb-detail__info-value">{value}</span>
    </div>
  );
}

const SCORE_TOOLTIP_ITEMS: Record<string, { title: string; items: string[] }> = {
  'Commercial Score': {
    title: 'How Commercial Score is calculated',
    items: [
      'Proximity to commercial hubs, markets & retail zones',
      'Estimated daily footfall & consumer density',
      'Area purchasing power & household income index',
      'Number of active businesses within 500 m radius',
      'Visibility from main commercial entry / exit roads',
      'Presence of anchors: shopping malls, banks, hospitals',
      'Distance to nearest high-street or bazaar cluster',
      'Seasonal demand fluctuation of the surrounding area',
      'Assessed by our ground survey team — scale 0–100',
    ],
  },
  'Traffic Score': {
    title: 'How Traffic Score is calculated',
    items: [
      'Average daily vehicle count (ADT) from road surveys',
      'Pedestrian flow volume at peak & off-peak hours',
      'Road classification: primary, secondary or arterial',
      'Number of lanes & effective carriageway width',
      'Intersection frequency & traffic signal density',
      'Public transport ridership at nearby bus/rail stops',
      'Dwell time — speed of traffic past the billboard face',
      'Congestion index during morning & evening rush hours',
      'Based on traffic survey & GIS data — scale 0–100',
    ],
  },
};

function ScoreCard({ label, value }: { label: string; value?: string }) {
  if (!value) return null;
  const tooltip = SCORE_TOOLTIP_ITEMS[label];
  return (
    <div className="bb-detail__score">
      {tooltip && (
        <span className="bb-detail__score-info">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="16" x2="12" y2="12" />
            <line x1="12" y1="8" x2="12.01" y2="8" />
          </svg>
          <span className="bb-detail__score-tooltip">
            <strong>{tooltip.title}</strong>
            <ul>
              {tooltip.items.map((item) => (
                <li key={item}>{item}</li>
              ))}
            </ul>
          </span>
        </span>
      )}
      <span className="bb-detail__score-value">{value}</span>
      <span className="bb-detail__score-label">{label}</span>
    </div>
  );
}

/** Haversine distance in metres between two lat/lng points */
function distanceMetres(lat1: number, lng1: number, lat2: number, lng2: number) {
  const R = 6371000;
  const toRad = (d: number) => (d * Math.PI) / 180;
  const dLat = toRad(lat2 - lat1);
  const dLng = toRad(lng2 - lng1);
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

export default async function BillboardDetailPage({ params }: PageProps) {
  const { uuid } = await params;

  let billboard: Billboard;
  try {
    const res = await billboardAPI.getByUuid(uuid);
    billboard = res.data;
  } catch {
    notFound();
  }

  const b = billboard;
  const hasCoords = b.latitude && b.longitude;
  const dimensions = b.width_ft && b.height_ft ? `${b.width_ft}' × ${b.height_ft}'` : b.display_size;
  const hasScores = b.commercial_score || b.traffic_score;

  // Fetch all billboards for related sections
  let sameOwner: Billboard[] = [];
  let nearby: Billboard[] = [];
  let sameAreaType: Billboard[] = [];

  try {
    const allRes = await billboardAPI.list({ limit: 500 });
    const all = (allRes.data.billboards || []).filter((item) => item.id !== b.id);

    // Same owner / agent
    if (b.owner_organization?.id) {
      sameOwner = all
        .filter((item) => item.owner_organization?.id === b.owner_organization?.id)
        .slice(0, 4);
    }

    // Nearby within 100 m
    if (hasCoords) {
      const lat = Number(b.latitude);
      const lng = Number(b.longitude);
      nearby = all
        .filter((item) => {
          if (!item.latitude || !item.longitude) return false;
          return distanceMetres(lat, lng, Number(item.latitude), Number(item.longitude)) <= 100;
        })
        .slice(0, 4);
    }

    // Same area / zone
    if (b.area_zone?.id) {
      sameAreaType = all
        .filter((item) => item.area_zone?.id === b.area_zone?.id)
        .slice(0, 4);
    }
  } catch {
    // Silently fail — related sections just won't render
  }

  return (
    <div className="min-h-screen">
      <Header />

      <main>
        <Breadcrumb items={[
          { label: 'Billboards', href: '/billboards' },
          { label: b.title },
        ]} />
        <div className="bb-detail container">
          {/* Two-column grid */}
          <div className="bb-detail__grid">
            {/* Left column */}
            <div className="bb-detail__main">
              {/* Gallery */}
              <BillboardGallery heroImage={b.hero_image} gallery={b.gallery} />

              {/* Title & badges */}
              <div className="bb-detail__title-block">
                <h1 className="bb-detail__title">{b.title}</h1>
                <div className="bb-detail__subtitle">
                  {b.billboard_id && <span className="bb-detail__id">#{b.billboard_id}</span>}
                  {b.district?.label && <span>{b.district.label}</span>}
                  {b.division?.label && <span>{b.division.label}</span>}
                </div>
                <div className="bb-detail__badges">
                  {b.availability_status?.label && (
                    <span className={`bb-detail__badge ${availabilityBadgeClass(b.availability_status.label)}`}>
                      {b.availability_status.label}
                    </span>
                  )}
                  {b.is_premium === '1' && (
                    <span className="bb-detail__badge bb-detail__badge--premium">Premium</span>
                  )}
                  {b.media_format?.label && (
                    <span className="bb-detail__badge bb-detail__badge--format">{b.media_format.label}</span>
                  )}
                </div>
              </div>

              {/* Specifications */}
              <div className="bb-detail__info-section">
                <h2 className="bb-detail__info-title">Specifications</h2>
                <div className="bb-detail__info-grid">
                  <InfoItem label="Media Format" value={b.media_format?.label} />
                  <InfoItem label="Placement Type" value={b.placement_type?.label} />
                  <InfoItem label="Dimensions" value={dimensions} />
                  <InfoItem label="Facing Direction" value={b.facing_direction} />
                  <InfoItem label="Road Type" value={b.road_type?.label} />
                  <InfoItem label="Road Name" value={b.road_name?.label} />
                </div>
              </div>

              {/* Video Section */}
              <BillboardVideoSection
                title="Area Walkthrough"
                subtitle={`Explore the surroundings of ${b.area_zone?.label || b.district?.label || 'this location'}`}
              />

              {/* Location */}
              <div className="bb-detail__info-section">
                <h2 className="bb-detail__info-title">Location</h2>
                <div className="bb-detail__info-grid">
                  <InfoItem label="Division" value={b.division?.label} />
                  <InfoItem label="District" value={b.district?.label} />
                  <InfoItem label="Area / Zone" value={b.area_zone?.label} />
                  <InfoItem label="Road" value={b.road_name?.label} />
                </div>
                {hasCoords && (
                  <div className="bb-detail__map">
                    <BillboardLocationMap
                      latitude={Number(b.latitude)}
                      longitude={Number(b.longitude)}
                      title={b.title}
                    />
                  </div>
                )}
              </div>

            </div>

            {/* Right sidebar */}
            <aside className="bb-detail__sidebar">
              {/* Pricing card */}
              <div className="bb-detail__price-card">
                <div>
                  <span className="bb-detail__price-label">Rate Card Price</span>
                  <div className="bb-detail__price-value">
                    {formatPrice(b.rate_card_price, b.currency) || 'Contact for pricing'}
                    {b.currency && <span className="bb-detail__price-currency"> {b.currency}</span>}
                  </div>
                  {b.booking_mode?.label && (
                    <span className="bb-detail__price-mode">{b.booking_mode.label}</span>
                  )}
                </div>
                <button className="bb-detail__cta" type="button">
                  Request Booking
                </button>
                <button className="bb-detail__cta bb-detail__cta--outline" type="button">
                  Contact Owner
                </button>
              </div>

              {/* Booking Calendar */}
              {/* TODO: Replace static bookedDates with real API data */}
              <BookingCalendar
                bookingMode={b.booking_mode?.label}
                bookedDates={[
                  '2026-04-03', '2026-04-04', '2026-04-05', '2026-04-06',
                  '2026-04-07', '2026-04-08', '2026-04-09',
                  '2026-04-14', '2026-04-15',
                  '2026-04-22', '2026-04-23', '2026-04-24', '2026-04-25', '2026-04-26',
                  '2026-05-01', '2026-05-02', '2026-05-03',
                  '2026-05-12', '2026-05-13', '2026-05-14', '2026-05-15', '2026-05-16',
                  '2026-05-17', '2026-05-18',
                ]}
              />

              {/* Owner card */}
              {b.owner_organization?.label && (
                <div className="bb-detail__owner-card">
                  <span className="bb-detail__owner-label">Listed by</span>
                  <span className="bb-detail__owner-name">{b.owner_organization.label}</span>
                </div>
              )}

              {/* Quick facts */}
              <div className="bb-detail__quick-facts">
                {b.billboard_id && (
                  <div className="bb-detail__quick-fact">
                    <span className="bb-detail__quick-fact-label">Billboard ID</span>
                    <span className="bb-detail__quick-fact-value">{b.billboard_id}</span>
                  </div>
                )}
                {dimensions && (
                  <div className="bb-detail__quick-fact">
                    <span className="bb-detail__quick-fact-label">Size</span>
                    <span className="bb-detail__quick-fact-value">{dimensions}</span>
                  </div>
                )}
                {b.facing_direction && (
                  <div className="bb-detail__quick-fact">
                    <span className="bb-detail__quick-fact-label">Facing</span>
                    <span className="bb-detail__quick-fact-value">{b.facing_direction}</span>
                  </div>
                )}
                {b.is_active && (
                  <div className="bb-detail__quick-fact">
                    <span className="bb-detail__quick-fact-label">Active</span>
                    <span className="bb-detail__quick-fact-value">{b.is_active === '1' ? 'Yes' : 'No'}</span>
                  </div>
                )}
              </div>

              {/* Performance Scores */}
              {hasScores && (
                <div className="bb-detail__scores-card">
                  <h3 className="bb-detail__scores-card-title">Performance Scores</h3>
                  <div className="bb-detail__scores">
                    <ScoreCard label="Commercial Score" value={b.commercial_score} />
                    <ScoreCard label="Traffic Score" value={b.traffic_score} />
                  </div>
                </div>
              )}
            </aside>
          </div>

          {/* Related: same owner */}
          <RelatedBillboards
            title={`Other Billboards from ${b.owner_organization?.label || 'This Agent'}`}
            billboards={sameOwner}
          />

          {/* Related: nearby */}
          <RelatedBillboards
            title="Billboards Nearby (within 100m)"
            billboards={nearby}
          />

          {/* Related: same area */}
          <RelatedBillboards
            title={`Other Billboards in ${b.area_zone?.label || 'This Area'}`}
            billboards={sameAreaType}
          />
        </div>
      </main>

      <Footer />
      <Chatbot />
    </div>
  );
}
