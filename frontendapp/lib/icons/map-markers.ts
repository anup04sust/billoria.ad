/**
 * Dynamic SVG Map Marker Generator
 *
 * Generates Leaflet-compatible map markers combining:
 * - Status (pin color + overlay badge)
 * - Media Format (inner illustration)
 *
 * 7 statuses × 15 media formats = 105 combinations generated on-the-fly.
 *
 * Usage:
 *   import { createBillboardIcon } from '@/lib/icons/map-markers';
 *   const icon = createBillboardIcon('Available', 'Digital Billboard');
 *   <Marker icon={icon} position={[lat, lng]} />
 */

import L from 'leaflet';

// ─── Status Colors ────────────────────────────────────────────
export const STATUS_COLORS: Record<string, { pin: string; board: string; boardStroke: string }> = {
  'Available':               { pin: '#22C55E', board: '#16A34A', boardStroke: '#15803D' },
  'Reserved':                { pin: '#F59E0B', board: '#D97706', boardStroke: '#B45309' },
  'Booked':                  { pin: '#EF4444', board: '#DC2626', boardStroke: '#B91C1C' },
  'Blocked':                 { pin: '#1F2937', board: '#374151', boardStroke: '#111827' },
  'Under Maintenance':       { pin: '#64748B', board: '#94A3B8', boardStroke: '#64748B' },
  'Temporarily Unavailable': { pin: '#F97316', board: '#EA580C', boardStroke: '#C2410C' },
  'Archived':                { pin: '#9CA3AF', board: '#D1D5DB', boardStroke: '#9CA3AF' },
};

// Premium uses a distinct purple
const PREMIUM_COLOR = { pin: '#8B5CF6', board: '#7C3AED', boardStroke: '#6D28D9' };

// Fallback for unknown statuses
const DEFAULT_COLOR = { pin: '#6366F1', board: '#4F46E5', boardStroke: '#4338CA' };

// ─── Pin Shell (viewBox 0 0 172 190) ──────────────────────────
const PIN_PATH = 'M86 18C56.2 18 32 42.2 32 72c0 42.8 46.2 83.5 51.9 88.3a3.8 3.8 0 0 0 4.9 0C94.8 155.5 141 114.8 141 72c0-29.8-24.2-54-55-54Z';

// ─── Status Badge SVGs (positioned at top-right of inner circle) ──
function statusBadge(status: string): string {
  switch (status) {
    case 'Available':
      return `<circle cx="110" cy="48" r="10" fill="#16A34A" stroke="white" stroke-width="2"/>
              <polyline points="105,48 108,51 115,44" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>`;
    case 'Reserved':
      return `<circle cx="110" cy="48" r="10" fill="#D97706" stroke="white" stroke-width="2"/>
              <line x1="110" y1="43" x2="110" y2="49" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
              <circle cx="110" cy="53" r="1.2" fill="white"/>`;
    case 'Booked':
      return `<circle cx="110" cy="48" r="10" fill="#DC2626" stroke="white" stroke-width="2"/>
              <polyline points="105,48 108,51 115,44" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>`;
    case 'Blocked':
      return `<circle cx="110" cy="48" r="10" fill="#1F2937" stroke="white" stroke-width="2"/>
              <line x1="106" y1="44" x2="114" y2="52" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
              <line x1="114" y1="44" x2="106" y2="52" stroke="white" stroke-width="2.5" stroke-linecap="round"/>`;
    case 'Under Maintenance':
      return `<circle cx="110" cy="48" r="10" fill="#64748B" stroke="white" stroke-width="2"/>
              <path d="M107 45l6 6M113 45l-6 6" stroke="#F59E0B" stroke-width="2.5" stroke-linecap="round"/>`;
    case 'Temporarily Unavailable':
      return `<circle cx="110" cy="48" r="10" fill="#EA580C" stroke="white" stroke-width="2"/>
              <line x1="110" y1="43" x2="110" y2="50" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
              <line x1="110" y1="43" x2="114" y2="47" stroke="white" stroke-width="2" stroke-linecap="round"/>`;
    case 'Archived':
      return `<circle cx="110" cy="48" r="10" fill="#9CA3AF" stroke="white" stroke-width="2"/>
              <path d="M106 46h8v2h-8z" fill="white"/>
              <path d="M107 48v4h6v-4" fill="none" stroke="white" stroke-width="1.5"/>`;
    default:
      return '';
  }
}

// ─── Media Format Inner Illustrations ─────────────────────────
// All drawn within the inner white circle (center 86,72 radius ~30)
function mediaFormatIllustration(
  format: string,
  boardColor: string,
  boardStroke: string
): string {
  switch (format) {
    case 'Static Billboard':
      return `<rect x="62" y="52" width="40" height="20" rx="3" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2.5"/>
              <rect x="83" y="72" width="6" height="22" rx="2" fill="#455A64"/>
              <path d="M86 74L95 80" stroke="#334155" stroke-width="2" stroke-linecap="round"/>
              <rect x="67" y="57" width="16" height="3.5" rx="1.5" fill="white"/>
              <rect x="67" y="63" width="10" height="2.5" rx="1.25" fill="white" opacity="0.85"/>`;

    case 'Digital Billboard':
      return `<rect x="58" y="50" width="48" height="24" rx="4" fill="#0F172A" stroke="#1E293B" stroke-width="2.5"/>
              <rect x="62" y="54" width="40" height="16" rx="2" fill="${boardColor}"/>
              <rect x="83" y="74" width="6" height="20" rx="2" fill="#455A64"/>
              <g opacity="0.5" fill="#93C5FD">
                <circle cx="68" cy="60" r="1.2"/><circle cx="74" cy="60" r="1.2"/>
                <circle cx="80" cy="60" r="1.2"/><circle cx="86" cy="60" r="1.2"/>
                <circle cx="92" cy="60" r="1.2"/><circle cx="98" cy="60" r="1.2"/>
                <circle cx="68" cy="66" r="1.2"/><circle cx="74" cy="66" r="1.2"/>
                <circle cx="80" cy="66" r="1.2"/><circle cx="86" cy="66" r="1.2"/>
                <circle cx="92" cy="66" r="1.2"/><circle cx="98" cy="66" r="1.2"/>
              </g>`;

    case 'LED Screen':
      return `<rect x="60" y="50" width="44" height="26" rx="3" fill="#0F172A" stroke="#1E293B" stroke-width="2.5"/>
              <rect x="64" y="54" width="36" height="18" rx="2" fill="${boardColor}"/>
              <rect x="79" y="76" width="8" height="16" rx="2" fill="#455A64"/>
              <line x1="74" y1="92" x2="92" y2="92" stroke="#455A64" stroke-width="3" stroke-linecap="round"/>
              <g opacity="0.4" fill="white">
                <rect x="67" y="57" width="3" height="3" rx="0.5"/>
                <rect x="73" y="57" width="3" height="3" rx="0.5"/>
                <rect x="79" y="57" width="3" height="3" rx="0.5"/>
                <rect x="85" y="57" width="3" height="3" rx="0.5"/>
                <rect x="91" y="57" width="3" height="3" rx="0.5"/>
                <rect x="67" y="63" width="3" height="3" rx="0.5"/>
                <rect x="73" y="63" width="3" height="3" rx="0.5"/>
                <rect x="79" y="63" width="3" height="3" rx="0.5"/>
                <rect x="85" y="63" width="3" height="3" rx="0.5"/>
                <rect x="91" y="63" width="3" height="3" rx="0.5"/>
              </g>`;

    case 'Unipole':
      return `<rect x="82" y="58" width="8" height="36" rx="3" fill="#455A64"/>
              <rect x="58" y="40" width="56" height="18" rx="3" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2.5"/>
              <path d="M86 58V60" stroke="#334155" stroke-width="2.5" stroke-linecap="round"/>
              <rect x="64" y="45" width="20" height="3" rx="1.5" fill="white" opacity="0.9"/>
              <rect x="64" y="51" width="14" height="2.5" rx="1.25" fill="white" opacity="0.7"/>`;

    case 'Wall Sign':
      return `<rect x="56" y="44" width="52" height="44" rx="2" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="2"/>
              <rect x="62" y="50" width="40" height="18" rx="2" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2"/>
              <rect x="67" y="55" width="16" height="3" rx="1.5" fill="white"/>
              <rect x="67" y="61" width="10" height="2.5" rx="1.25" fill="white" opacity="0.8"/>
              <rect x="64" y="74" width="12" height="10" rx="1" fill="#CBD5E1"/>
              <rect x="80" y="74" width="12" height="10" rx="1" fill="#CBD5E1"/>`;

    case 'Rooftop Sign':
      return `<path d="M60 68L86 48l26 20" fill="#94A3B8" stroke="#64748B" stroke-width="2"/>
              <rect x="64" y="68" width="44" height="26" rx="1" fill="#E2E8F0"/>
              <rect x="62" y="40" width="48" height="16" rx="3" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2.5"/>
              <rect x="68" y="45" width="18" height="3" rx="1.5" fill="white"/>
              <rect x="68" y="51" width="12" height="2.5" rx="1.25" fill="white" opacity="0.8"/>
              <rect x="76" y="72" width="10" height="14" rx="1" fill="#CBD5E1"/>`;

    case 'Gantry Billboard':
      return `<rect x="58" y="48" width="56" height="18" rx="3" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2.5"/>
              <line x1="62" y1="66" x2="62" y2="92" stroke="#455A64" stroke-width="4" stroke-linecap="round"/>
              <line x1="110" y1="66" x2="110" y2="92" stroke="#455A64" stroke-width="4" stroke-linecap="round"/>
              <rect x="64" y="53" width="18" height="3" rx="1.5" fill="white"/>
              <rect x="64" y="58" width="12" height="2.5" rx="1.25" fill="white" opacity="0.8"/>`;

    case 'Bridge Banner':
      return `<path d="M58 52c0-8 12-14 28-14s28 6 28 14" fill="none" stroke="#455A64" stroke-width="3"/>
              <rect x="64" y="52" width="44" height="16" rx="2" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2"/>
              <line x1="62" y1="52" x2="62" y2="88" stroke="#455A64" stroke-width="3.5" stroke-linecap="round"/>
              <line x1="110" y1="52" x2="110" y2="88" stroke="#455A64" stroke-width="3.5" stroke-linecap="round"/>
              <rect x="70" y="56" width="16" height="3" rx="1.5" fill="white"/>
              <rect x="70" y="62" width="10" height="2.5" rx="1.25" fill="white" opacity="0.8"/>`;

    case 'Building Wrap':
      return `<rect x="60" y="42" width="44" height="50" rx="2" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="2"/>
              <rect x="62" y="44" width="40" height="22" rx="1" fill="${boardColor}" opacity="0.85" stroke="${boardStroke}" stroke-width="1.5" stroke-dasharray="4 2"/>
              <rect x="66" y="49" width="16" height="3" rx="1.5" fill="white"/>
              <rect x="66" y="55" width="10" height="2.5" rx="1.25" fill="white" opacity="0.8"/>
              <rect x="66" y="72" width="10" height="14" rx="1" fill="#CBD5E1"/>
              <rect x="82" y="72" width="10" height="14" rx="1" fill="#CBD5E1"/>`;

    case 'Pole Sign':
      return `<rect x="83" y="60" width="6" height="34" rx="2" fill="#455A64"/>
              <rect x="68" y="44" width="36" height="16" rx="3" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2.5"/>
              <circle cx="86" cy="94" r="3" fill="#455A64"/>
              <rect x="73" y="49" width="14" height="3" rx="1.5" fill="white"/>
              <rect x="73" y="54" width="9" height="2.5" rx="1.25" fill="white" opacity="0.8"/>`;

    case 'Lamppost Branding':
      return `<line x1="86" y1="38" x2="86" y2="96" stroke="#455A64" stroke-width="4" stroke-linecap="round"/>
              <circle cx="86" cy="38" r="4" fill="#FACC15" stroke="#EAB308" stroke-width="1.5"/>
              <rect x="74" y="54" width="24" height="30" rx="2" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2"/>
              <rect x="78" y="60" width="12" height="3" rx="1.5" fill="white"/>
              <rect x="78" y="66" width="8" height="2.5" rx="1.25" fill="white" opacity="0.8"/>`;

    case 'Median Panel':
      return `<line x1="46" y1="88" x2="126" y2="88" stroke="#94A3B8" stroke-width="3"/>
              <line x1="46" y1="46" x2="126" y2="46" stroke="#94A3B8" stroke-width="3"/>
              <line x1="86" y1="46" x2="86" y2="88" stroke="#CBD5E1" stroke-width="2" stroke-dasharray="4 3"/>
              <rect x="64" y="56" width="36" height="18" rx="2" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2"/>
              <line x1="75" y1="74" x2="75" y2="88" stroke="#455A64" stroke-width="3" stroke-linecap="round"/>
              <line x1="97" y1="74" x2="97" y2="88" stroke="#455A64" stroke-width="3" stroke-linecap="round"/>
              <rect x="69" y="61" width="14" height="3" rx="1.5" fill="white"/>`;

    case 'Foot Over Bridge Panel':
      return `<line x1="60" y1="60" x2="60" y2="92" stroke="#455A64" stroke-width="3.5" stroke-linecap="round"/>
              <line x1="112" y1="60" x2="112" y2="92" stroke="#455A64" stroke-width="3.5" stroke-linecap="round"/>
              <path d="M60 60h8l6-10h24l6 10h8" fill="none" stroke="#455A64" stroke-width="2.5" stroke-linejoin="round"/>
              <rect x="66" y="62" width="40" height="14" rx="2" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2"/>
              <rect x="72" y="66" width="16" height="3" rx="1.5" fill="white"/>
              <rect x="72" y="72" width="10" height="2.5" rx="1.25" fill="white" opacity="0.8"/>`;

    case 'Transit Shelter Branding':
      return `<path d="M58 54l6-8h48l6 8" fill="none" stroke="#455A64" stroke-width="2.5" stroke-linejoin="round"/>
              <line x1="60" y1="54" x2="60" y2="90" stroke="#455A64" stroke-width="3" stroke-linecap="round"/>
              <line x1="112" y1="54" x2="112" y2="90" stroke="#455A64" stroke-width="3" stroke-linecap="round"/>
              <line x1="58" y1="90" x2="114" y2="90" stroke="#455A64" stroke-width="2.5"/>
              <rect x="64" y="56" width="44" height="22" rx="2" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2"/>
              <rect x="70" y="61" width="18" height="3" rx="1.5" fill="white"/>
              <rect x="70" y="67" width="12" height="2.5" rx="1.25" fill="white" opacity="0.8"/>
              <path d="M80 90v-8h12v8" fill="none" stroke="#455A64" stroke-width="2"/>`;

    case 'Directional Signage':
      return `<line x1="86" y1="40" x2="86" y2="94" stroke="#455A64" stroke-width="4" stroke-linecap="round"/>
              <path d="M66 50h30l8 6-8 6H66z" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2"/>
              <path d="M106 68H76l-8 6 8 6h30z" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2" opacity="0.8"/>
              <rect x="72" y="54" width="14" height="2.5" rx="1.25" fill="white"/>
              <rect x="82" y="72" width="14" height="2.5" rx="1.25" fill="white"/>`;

    default:
      // Fallback: generic rectangular board
      return `<rect x="62" y="52" width="40" height="20" rx="3" fill="${boardColor}" stroke="${boardStroke}" stroke-width="2.5"/>
              <rect x="83" y="72" width="6" height="22" rx="2" fill="#455A64"/>
              <rect x="67" y="57" width="16" height="3.5" rx="1.5" fill="white"/>
              <rect x="67" y="63" width="10" height="2.5" rx="1.25" fill="white" opacity="0.85"/>`;
  }
}

// ─── Premium Star Overlay ─────────────────────────────────────
function premiumStar(): string {
  return `<path d="M86 43l2.2 4.6 5.1.7-3.7 3.6.9 5-4.5-2.4-4.5 2.4.9-5-3.7-3.6 5.1-.7L86 43Z" fill="#FACC15" stroke="#EAB308" stroke-width="1"/>`;
}

// ─── Generate Complete Marker SVG ─────────────────────────────
export function generateMarkerSVG(
  status: string,
  mediaFormat: string,
  isPremium = false
): string {
  const colors = isPremium
    ? PREMIUM_COLOR
    : STATUS_COLORS[status] || DEFAULT_COLOR;

  const pinColor = isPremium ? PREMIUM_COLOR.pin : colors.pin;

  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 172 170" fill="none">
    <!-- Pin shell -->
    <path d="${PIN_PATH}" fill="${pinColor}"/>
    <!-- Inner white circle -->
    <circle cx="86" cy="72" r="35" fill="white"/>
    <!-- Media format illustration -->
    ${mediaFormatIllustration(mediaFormat, colors.board, colors.boardStroke)}
    <!-- Premium star -->
    ${isPremium ? premiumStar() : ''}
    <!-- Status badge -->
    ${statusBadge(status)}
  </svg>`;
}

// ─── SVG to Data URI ──────────────────────────────────────────
function svgToDataUri(svg: string): string {
  const encoded = encodeURIComponent(svg)
    .replace(/%20/g, ' ')
    .replace(/%22/g, "'")
    .replace(/%3D/g, '=')
    .replace(/%3A/g, ':')
    .replace(/%2F/g, '/');
  return `data:image/svg+xml,${encoded}`;
}

// ─── Create Leaflet Icon ──────────────────────────────────────
const iconCache = new Map<string, L.Icon>();

export function createBillboardIcon(
  status: string,
  mediaFormat: string,
  isPremium = false,
  size: [number, number] = [60, 73]
): L.Icon {
  const cacheKey = `${status}|${mediaFormat}|${isPremium}`;

  if (iconCache.has(cacheKey)) {
    return iconCache.get(cacheKey)!;
  }

  const svg = generateMarkerSVG(status, mediaFormat, isPremium);
  const icon = L.icon({
    iconUrl: svgToDataUri(svg),
    iconSize: size,
    iconAnchor: [size[0] / 2, size[1]],
    popupAnchor: [0, -size[1]],
  });

  iconCache.set(cacheKey, icon);
  return icon;
}

// ─── Helper: Get icon for a Billboard object ─────────────────
export function getBillboardMarkerIcon(billboard: {
  availability_status?: { label: string };
  media_format?: { label: string };
  is_premium?: string;
}): L.Icon {
  const status = billboard.availability_status?.label || 'Available';
  const mediaFormat = billboard.media_format?.label || 'Static Billboard';
  const isPremium = billboard.is_premium === '1' || billboard.is_premium === 'true';

  return createBillboardIcon(status, mediaFormat, isPremium);
}

// ─── All available values for legend/filters ──────────────────
export const STATUSES = Object.keys(STATUS_COLORS);
export const MEDIA_FORMATS = [
  'Static Billboard', 'Digital Billboard', 'LED Screen', 'Unipole',
  'Wall Sign', 'Rooftop Sign', 'Gantry Billboard', 'Bridge Banner',
  'Building Wrap', 'Pole Sign', 'Lamppost Branding', 'Median Panel',
  'Foot Over Bridge Panel', 'Transit Shelter Branding', 'Directional Signage',
];
