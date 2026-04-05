'use client';

import { useMap } from 'react-leaflet';
import { useState, useRef, useCallback } from 'react';
import L from 'leaflet';
import { IconZoomIn, IconZoomOut, IconCrosshair, IconFilter, IconList } from '@/lib/icons/ui-icons';

/* ── Human location marker icon (SVG data‑URI) ── */
const HUMAN_MARKER_SVG = `<svg xmlns="http://www.w3.org/2000/svg" width="40" height="56" viewBox="0 0 40 56">
  <!-- pin shape -->
  <path d="M20 54 C20 54 2 33 2 20 A18 18 0 1 1 38 20 C38 33 20 54 20 54Z"
        fill="#1D4ED8" stroke="#fff" stroke-width="2"/>
  <!-- head -->
  <circle cx="20" cy="15" r="5" fill="#fff"/>
  <!-- body -->
  <path d="M14 23 Q14 20 20 20 Q26 20 26 23 L25 30 H15 Z" fill="#fff"/>
</svg>`;

const humanIcon = L.icon({
  iconUrl: `data:image/svg+xml;charset=utf-8,${encodeURIComponent(HUMAN_MARKER_SVG)}`,
  iconSize: [40, 56],
  iconAnchor: [20, 56],
  popupAnchor: [0, -56],
});

interface MapControlsProps {
  onFilterClick?: () => void;
  onListViewClick?: () => void;
  filterActive?: boolean;
  listViewActive?: boolean;
}

export function MapControls({ onFilterClick, onListViewClick, filterActive, listViewActive }: MapControlsProps) {
  const map = useMap();
  const [locating, setLocating] = useState(false);
  const markerRef = useRef<L.Marker | null>(null);
  const pulseRef = useRef<L.CircleMarker | null>(null);

  const handleZoomIn = () => {
    map.zoomIn();
  };

  const handleZoomOut = () => {
    map.zoomOut();
  };

  const handleNearMe = useCallback(() => {
    if (!navigator.geolocation) return;

    setLocating(true);
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        const latlng: L.LatLngExpression = [latitude, longitude];

        map.flyTo(latlng, 14, { duration: 1.5 });

        // Remove previous marker & pulse
        if (markerRef.current) markerRef.current.remove();
        if (pulseRef.current) pulseRef.current.remove();

        // Add pulsing accuracy circle
        pulseRef.current = L.circleMarker(latlng, {
          radius: 18,
          weight: 2,
          color: '#1D4ED8',
          fillColor: '#1D4ED8',
          fillOpacity: 0.15,
          className: 'user-location-pulse',
        }).addTo(map);

        // Add human marker
        markerRef.current = L.marker(latlng, { icon: humanIcon })
          .addTo(map)
          .bindPopup('You are here');

        setLocating(false);
      },
      () => {
        setLocating(false);
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }, [map]);

  const handleFilter = () => {
    onFilterClick?.();
  };

  return (
    <div className="map-controls">
      <button
        className="map-controls__btn"
        onClick={handleZoomIn}
        title="Zoom In"
        aria-label="Zoom in"
      >
        <IconZoomIn />
      </button>

      <button
        className="map-controls__btn"
        onClick={handleZoomOut}
        title="Zoom Out"
        aria-label="Zoom out"
      >
        <IconZoomOut />
      </button>

      <button
        className={`map-controls__btn ${locating ? 'map-controls__btn--active' : ''}`}
        onClick={handleNearMe}
        title="Near Me"
        aria-label="Find my location"
      >
        <IconCrosshair />
      </button>

      <button
        className={`map-controls__btn ${filterActive ? 'map-controls__btn--filter-active' : ''}`}
        onClick={handleFilter}
        title="Filter"
        aria-label="Filter billboards"
      >
        <IconFilter />
      </button>

      <button
        className={`map-controls__btn ${listViewActive ? 'map-controls__btn--active-toggle' : ''}`}
        onClick={() => onListViewClick?.()}
        title="List View"
        aria-label="Switch to list view"
      >
        <IconList />
      </button>
    </div>
  );
}
