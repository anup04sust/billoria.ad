'use client';

import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import { MapControls } from './MapControls';
import { MapFilterModal, EMPTY_FILTERS } from './MapFilterModal';
import type { MapFilters } from './MapFilterModal';
import MarkerClusterGroup from 'react-leaflet-cluster';
import { useEffect, useState, useMemo, useCallback } from 'react';
import type { Billboard } from '@/types/billboard';
import { billboardAPI } from '@/lib/api/billboard';
import { getBillboardMarkerIcon } from '@/lib/icons/map-markers';
import './billboard-map.css';
import { MapSkeleton } from './MapSkeleton';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

interface BillboardMapProps {
  billboards?: Billboard[];
}

export function BillboardMap({ billboards: propBillboards }: BillboardMapProps) {
  const [isMounted, setIsMounted] = useState(false);
  const [billboards, setBillboards] = useState<Billboard[]>(propBillboards || []);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [filterOpen, setFilterOpen] = useState(false);
  const [filters, setFilters] = useState<MapFilters>(EMPTY_FILTERS);

  useEffect(() => {
    setIsMounted(true);

    // Fetch from API if no billboards provided or empty array
    const shouldFetch = !propBillboards || propBillboards.length === 0;
    console.log('Should fetch from API?', shouldFetch, 'Props:', propBillboards?.length || 0);
    
    if (shouldFetch) {
      fetchBillboards();
    }
  }, [propBillboards]);

  const fetchBillboards = async () => {
    try {
      setIsLoading(true);
      setError(null);
      console.log('Fetching billboards from API...');
      console.log('Environment:', {
        NEXT_PUBLIC_API_BASE_URL: process.env.NEXT_PUBLIC_API_BASE_URL,
        NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL,
      });
      
      const data = await billboardAPI.listForMap();
      console.log('Billboards fetched:', data.length, data);
      setBillboards(data);
    } catch (err) {
      console.error('Failed to fetch billboards:', err);
      console.error('Error details:', {
        name: (err as Error).name,
        message: (err as Error).message,
        stack: (err as Error).stack,
      });
      setError('Failed to load billboard locations. Check console for details.');
    } finally {
      setIsLoading(false);
    }
  };

  // Default center: Bangladesh (country-wide view)
  const defaultCenter: [number, number] = [23.685, 90.356];
  const defaultZoom = 8;

  // Get valid billboards with coordinates
  const validBillboards = useMemo(
    () => billboards.filter((b) => b.latitude && b.longitude),
    [billboards]
  );

  /* ── Client-side filter logic ── */
  const filteredBillboards = useMemo(() => {
    return validBillboards.filter((b) => {
      // Search
      if (filters.search) {
        const q = filters.search.toLowerCase();
        const matchTitle = b.title?.toLowerCase().includes(q);
        const matchId = b.billboard_id?.toLowerCase().includes(q);
        const matchZone = b.area_zone?.label?.toLowerCase().includes(q);
        if (!matchTitle && !matchId && !matchZone) return false;
      }
      // Divisions
      if (filters.divisions.length > 0) {
        if (!b.division?.label || !filters.divisions.includes(b.division.label)) return false;
      }
      // Districts
      if (filters.districts.length > 0) {
        if (!b.district?.label || !filters.districts.includes(b.district.label)) return false;
      }
      // Road types
      if (filters.roadTypes.length > 0) {
        if (!b.road_type?.label || !filters.roadTypes.includes(b.road_type.label)) return false;
      }
      // Road names
      if (filters.roadNames.length > 0) {
        if (!b.road_name?.label || !filters.roadNames.includes(b.road_name.label)) return false;
      }
      // Media formats
      if (filters.mediaFormats.length > 0) {
        if (!b.media_format?.label || !filters.mediaFormats.includes(b.media_format.label)) return false;
      }
      // Placement types
      if (filters.placementTypes.length > 0) {
        if (!b.placement_type?.label || !filters.placementTypes.includes(b.placement_type.label)) return false;
      }
      // Statuses
      if (filters.statuses.length > 0) {
        if (!b.availability_status?.label || !filters.statuses.includes(b.availability_status.label)) return false;
      }
      return true;
    });
  }, [validBillboards, filters]);

  const handleApplyFilters = useCallback((f: MapFilters) => setFilters(f), []);

  if (!isMounted || isLoading) {
    return <MapSkeleton />;
  }

  if (error) {
    return (
      <section className="billboard-map-section">
        <div className="billboard-map-error">
          <p>{error}</p>
          <button onClick={fetchBillboards}>Retry</button>
        </div>
      </section>
    );
  }

  console.log('Total billboards:', billboards.length);
  console.log('Valid billboards with coordinates:', validBillboards.length);
  console.log('Filtered billboards:', filteredBillboards.length);

  return (
    <section className="billboard-map-section">
      {filteredBillboards.length > 0 && (
        <div className="billboard-map-counter">
          {filteredBillboards.length} billboard{filteredBillboards.length !== 1 ? 's' : ''} found
        </div>
      )}
      <MapFilterModal
        open={filterOpen}
        filters={filters}
        billboards={validBillboards}
        onClose={() => setFilterOpen(false)}
        onApply={handleApplyFilters}
      />
      <MapContainer
        center={defaultCenter}
        zoom={defaultZoom}
        className="billboard-map"
        scrollWheelZoom={false}
        zoomControl={false}
      >
        <MapControls
          onFilterClick={() => setFilterOpen(true)}
          filterActive={
            filters.search !== '' ||
            filters.divisions.length > 0 ||
            filters.districts.length > 0 ||
            filters.roadTypes.length > 0 ||
            filters.roadNames.length > 0 ||
            filters.mediaFormats.length > 0 ||
            filters.placementTypes.length > 0 ||
            filters.statuses.length > 0
          }
        />
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />

        <MarkerClusterGroup
          chunkedLoading
          maxClusterRadius={60}
          spiderfyOnMaxZoom={true}
          showCoverageOnHover={true}
          zoomToBoundsOnClick={true}
        >
          {filteredBillboards.map((billboard) => {
            const lat = parseFloat(billboard.latitude!);
            const lng = parseFloat(billboard.longitude!);

            if (isNaN(lat) || isNaN(lng)) return null;

            return (
              <Marker key={billboard.id} position={[lat, lng]} icon={getBillboardMarkerIcon(billboard)}>
                <Popup className="billboard-map__popup">
                  <div className="billboard-map-card">
                    {billboard.hero_image && (
                      <div className="billboard-map-card__image">
                        <img
                          src={billboard.hero_image.thumbnail}
                          alt={billboard.hero_image.alt}
                          className="billboard-map-card__img"
                        />
                      </div>
                    )}
                    <div className="billboard-map-card__content">
                      <h4 className="billboard-map-card__title">{billboard.title}</h4>
                      {billboard.area_zone && (
                        <p className="billboard-map-card__location">
                          {billboard.area_zone.label}
                        </p>
                      )}
                      {billboard.rate_card_price && (
                        <p className="billboard-map-card__price">
                          BDT {parseFloat(billboard.rate_card_price).toLocaleString()}
                        </p>
                      )}
                      <a href={`/billboard/${billboard.id}`} className="billboard-map-card__link">
                        View Details →
                      </a>
                    </div>
                  </div>
                </Popup>
              </Marker>
            );
          })}
        </MarkerClusterGroup>
      </MapContainer>
    </section>
  );
}
