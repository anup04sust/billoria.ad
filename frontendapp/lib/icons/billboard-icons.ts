/**
 * Billboard Icon Map
 *
 * Maps Drupal taxonomy term names and billboard field names to SVG icon paths.
 * All icons are in /public/icons/ and can be used as:
 *   <img src={iconMap.mediaFormat['Digital Billboard']} alt="Digital Billboard" />
 *   or via Next.js Image component.
 */

const ICON_BASE = '/icons';

/** Media Format (field_media_format) — 15 terms */
export const mediaFormatIcons: Record<string, string> = {
  'Bridge Banner': `${ICON_BASE}/media-format/bridge-banner.svg`,
  'Building Wrap': `${ICON_BASE}/media-format/building-wrap.svg`,
  'Digital Billboard': `${ICON_BASE}/media-format/digital-billboard.svg`,
  'Directional Signage': `${ICON_BASE}/media-format/directional-signage.svg`,
  'Foot Over Bridge Panel': `${ICON_BASE}/media-format/foot-over-bridge-panel.svg`,
  'Gantry Billboard': `${ICON_BASE}/media-format/gantry-billboard.svg`,
  'Lamppost Branding': `${ICON_BASE}/media-format/lamppost-branding.svg`,
  'LED Screen': `${ICON_BASE}/media-format/led-screen.svg`,
  'Median Panel': `${ICON_BASE}/media-format/median-panel.svg`,
  'Pole Sign': `${ICON_BASE}/media-format/pole-sign.svg`,
  'Rooftop Sign': `${ICON_BASE}/media-format/rooftop-sign.svg`,
  'Static Billboard': `${ICON_BASE}/media-format/static-billboard.svg`,
  'Transit Shelter Branding': `${ICON_BASE}/media-format/transit-shelter-branding.svg`,
  'Unipole': `${ICON_BASE}/media-format/unipole.svg`,
  'Wall Sign': `${ICON_BASE}/media-format/wall-sign.svg`,
};

/** Placement Type (field_placement_type) — 17 terms */
export const placementTypeIcons: Record<string, string> = {
  'Bridge Side': `${ICON_BASE}/placement-type/bridge-side.svg`,
  'Building Facade': `${ICON_BASE}/placement-type/building-facade.svg`,
  'Bus Stand Area': `${ICON_BASE}/placement-type/bus-stand-area.svg`,
  'Entry Gate': `${ICON_BASE}/placement-type/entry-gate.svg`,
  'Exit Gate': `${ICON_BASE}/placement-type/exit-gate.svg`,
  'Flyover Side': `${ICON_BASE}/placement-type/flyover-side.svg`,
  'Flyover Underpass': `${ICON_BASE}/placement-type/flyover-underpass.svg`,
  'Intersection Corner': `${ICON_BASE}/placement-type/intersection-corner.svg`,
  'Market Front': `${ICON_BASE}/placement-type/market-front.svg`,
  'Median Strip': `${ICON_BASE}/placement-type/median-strip.svg`,
  'Rail Crossing Area': `${ICON_BASE}/placement-type/rail-crossing-area.svg`,
  'Road Divider': `${ICON_BASE}/placement-type/road-divider.svg`,
  'Roadside Left': `${ICON_BASE}/placement-type/roadside-left.svg`,
  'Roadside Right': `${ICON_BASE}/placement-type/roadside-right.svg`,
  'Rooftop': `${ICON_BASE}/placement-type/rooftop.svg`,
  'Roundabout': `${ICON_BASE}/placement-type/roundabout.svg`,
  'Toll Plaza Approach': `${ICON_BASE}/placement-type/toll-plaza-approach.svg`,
};

/** Illumination Type (field_illumination_type) — 6 terms */
export const illuminationTypeIcons: Record<string, string> = {
  'Back Lit': `${ICON_BASE}/illumination-type/back-lit.svg`,
  'Flood Lit': `${ICON_BASE}/illumination-type/flood-lit.svg`,
  'Front Lit': `${ICON_BASE}/illumination-type/front-lit.svg`,
  'LED Illuminated': `${ICON_BASE}/illumination-type/led-illuminated.svg`,
  'Non Illuminated': `${ICON_BASE}/illumination-type/non-illuminated.svg`,
  'Solar Lit': `${ICON_BASE}/illumination-type/solar-lit.svg`,
};

/** Availability Status (field_availability_status) — 7 terms */
export const availabilityStatusIcons: Record<string, string> = {
  'Archived': `${ICON_BASE}/availability-status/archived.svg`,
  'Available': `${ICON_BASE}/availability-status/available.svg`,
  'Blocked': `${ICON_BASE}/availability-status/blocked.svg`,
  'Booked': `${ICON_BASE}/availability-status/booked.svg`,
  'Reserved': `${ICON_BASE}/availability-status/reserved.svg`,
  'Temporarily Unavailable': `${ICON_BASE}/availability-status/temporarily-unavailable.svg`,
  'Under Maintenance': `${ICON_BASE}/availability-status/under-maintenance.svg`,
};

/** Booking Mode (field_booking_mode) — 7 terms */
export const bookingModeIcons: Record<string, string> = {
  'Cluster Booking': `${ICON_BASE}/booking-mode/cluster-booking.svg`,
  'Day Part Booking': `${ICON_BASE}/booking-mode/day-part-booking.svg`,
  'Full Unit Booking': `${ICON_BASE}/booking-mode/full-unit-booking.svg`,
  'Partial Unit Booking': `${ICON_BASE}/booking-mode/partial-unit-booking.svg`,
  'Road Takeover': `${ICON_BASE}/booking-mode/road-takeover.svg`,
  'Share of Voice': `${ICON_BASE}/booking-mode/share-of-voice.svg`,
  'Time Slot Booking': `${ICON_BASE}/booking-mode/time-slot-booking.svg`,
};

/** Visibility Class (field_visibility_class) — 5 terms */
export const visibilityClassIcons: Record<string, string> = {
  'High': `${ICON_BASE}/visibility-class/high.svg`,
  'Limited': `${ICON_BASE}/visibility-class/limited.svg`,
  'Medium': `${ICON_BASE}/visibility-class/medium.svg`,
  'Premium': `${ICON_BASE}/visibility-class/premium.svg`,
  'Standard': `${ICON_BASE}/visibility-class/standard.svg`,
};

/** Traffic Direction (field_traffic_direction) — 9 terms */
export const trafficDirectionIcons: Record<string, string> = {
  'Both Directions': `${ICON_BASE}/traffic-direction/both-directions.svg`,
  'Eastbound': `${ICON_BASE}/traffic-direction/eastbound.svg`,
  'Inbound': `${ICON_BASE}/traffic-direction/inbound.svg`,
  'Northbound': `${ICON_BASE}/traffic-direction/northbound.svg`,
  'One Way': `${ICON_BASE}/traffic-direction/one-way.svg`,
  'Outbound': `${ICON_BASE}/traffic-direction/outbound.svg`,
  'Southbound': `${ICON_BASE}/traffic-direction/southbound.svg`,
  'Two Way': `${ICON_BASE}/traffic-direction/two-way.svg`,
  'Westbound': `${ICON_BASE}/traffic-direction/westbound.svg`,
};

/** Billboard Field Icons — for UI labels and detail views */
export const billboardFieldIcons: Record<string, string> = {
  active: `${ICON_BASE}/billboard-fields/active.svg`,
  areaZone: `${ICON_BASE}/billboard-fields/area-zone.svg`,
  billboardId: `${ICON_BASE}/billboard-fields/billboard-id.svg`,
  commercialScore: `${ICON_BASE}/billboard-fields/commercial-score.svg`,
  contact: `${ICON_BASE}/billboard-fields/contact.svg`,
  currency: `${ICON_BASE}/billboard-fields/currency.svg`,
  displaySize: `${ICON_BASE}/billboard-fields/display-size.svg`,
  district: `${ICON_BASE}/billboard-fields/district.svg`,
  facingDirection: `${ICON_BASE}/billboard-fields/facing-direction.svg`,
  gallery: `${ICON_BASE}/billboard-fields/gallery.svg`,
  hasDivider: `${ICON_BASE}/billboard-fields/has-divider.svg`,
  height: `${ICON_BASE}/billboard-fields/height.svg`,
  heroImage: `${ICON_BASE}/billboard-fields/hero-image.svg`,
  laneCount: `${ICON_BASE}/billboard-fields/lane-count.svg`,
  location: `${ICON_BASE}/billboard-fields/location.svg`,
  notes: `${ICON_BASE}/billboard-fields/notes.svg`,
  organization: `${ICON_BASE}/billboard-fields/organization.svg`,
  owner: `${ICON_BASE}/billboard-fields/owner.svg`,
  premium: `${ICON_BASE}/billboard-fields/premium.svg`,
  price: `${ICON_BASE}/billboard-fields/price.svg`,
  trafficScore: `${ICON_BASE}/billboard-fields/traffic-score.svg`,
  visibilityDistance: `${ICON_BASE}/billboard-fields/visibility-distance.svg`,
  width: `${ICON_BASE}/billboard-fields/width.svg`,
};

/**
 * Get icon path for any taxonomy term by vocabulary and term name.
 */
export function getTaxonomyIcon(vocabulary: string, termName: string): string | undefined {
  const maps: Record<string, Record<string, string>> = {
    media_format: mediaFormatIcons,
    placement_type: placementTypeIcons,
    illumination_type: illuminationTypeIcons,
    availability_status: availabilityStatusIcons,
    booking_mode: bookingModeIcons,
    visibility_class: visibilityClassIcons,
    traffic_direction: trafficDirectionIcons,
  };
  return maps[vocabulary]?.[termName];
}
