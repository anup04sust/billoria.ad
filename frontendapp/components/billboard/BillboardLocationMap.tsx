'use client';

import { useEffect, useRef } from 'react';
import './billboard-location-map.css';

interface BillboardLocationMapProps {
  latitude: number;
  longitude: number;
  title?: string;
}

export function BillboardLocationMap({ latitude, longitude, title }: BillboardLocationMapProps) {
  const mapRef = useRef<HTMLDivElement>(null);
  const mapInstanceRef = useRef<L.Map | null>(null);

  useEffect(() => {
    if (!mapRef.current || mapInstanceRef.current) return;

    let cancelled = false;

    async function initMap() {
      const L = (await import('leaflet')).default;
      await import('leaflet/dist/leaflet.css');

      if (cancelled || !mapRef.current) return;

      const map = L.map(mapRef.current, {
        center: [latitude, longitude],
        zoom: 15,
        zoomControl: false,
        attributionControl: false,
        dragging: true,
        scrollWheelZoom: false,
      });

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
      }).addTo(map);

      const icon = L.divIcon({
        html: `<svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M16 0C7.163 0 0 7.163 0 16c0 12 16 24 16 24s16-12 16-24C32 7.163 24.837 0 16 0z" fill="var(--color-primary, #16a34a)"/>
          <circle cx="16" cy="15" r="6" fill="white"/>
        </svg>`,
        className: 'bb-location-marker',
        iconSize: [32, 40],
        iconAnchor: [16, 40],
      });

      L.marker([latitude, longitude], { icon }).addTo(map);
      if (title) {
        L.marker([latitude, longitude], { icon }).bindTooltip(title, { permanent: false });
      }

      mapInstanceRef.current = map;
    }

    initMap();

    return () => {
      cancelled = true;
      if (mapInstanceRef.current) {
        mapInstanceRef.current.remove();
        mapInstanceRef.current = null;
      }
    };
  }, [latitude, longitude, title]);

  return <div ref={mapRef} className="bb-location-map" />;
}
