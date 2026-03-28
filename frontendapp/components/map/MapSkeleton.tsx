import './map-skeleton.css';

export function MapSkeleton() {
  return (
    <section className="billboard-map-section">
      <div className="map-skeleton">
        {/* Animated grid background */}
        <div className="map-skeleton__grid" />
        
        {/* Shimmer overlay */}
        <div className="map-skeleton__overlay" />
        
        {/* Animated marker placeholders */}
        <div className="map-skeleton__markers">
          <div className="map-skeleton__marker" />
          <div className="map-skeleton__marker" />
          <div className="map-skeleton__marker" />
          <div className="map-skeleton__marker" />
          <div className="map-skeleton__marker" />
          <div className="map-skeleton__marker" />
        </div>
        
        {/* Mock map controls */}
        <div className="map-skeleton__controls">
          <div className="map-skeleton__control-btn" />
          <div className="map-skeleton__control-btn" />
        </div>
        
        {/* Loading text with spinner */}
        <div className="map-skeleton__text">
          <div className="map-skeleton__spinner" />
          <span>Loading interactive map...</span>
        </div>
      </div>
    </section>
  );
}
