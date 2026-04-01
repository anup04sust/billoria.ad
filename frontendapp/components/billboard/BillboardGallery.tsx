'use client';

import { useState } from 'react';
import './billboard-gallery.css';

interface GalleryImage {
  original: string;
  large: string;
  thumbnail: string;
  alt: string;
  title: string;
}

interface BillboardGalleryProps {
  heroImage?: GalleryImage & { medium: string };
  gallery?: GalleryImage[];
}

export function BillboardGallery({ heroImage, gallery = [] }: BillboardGalleryProps) {
  const allImages = heroImage ? [heroImage, ...gallery] : gallery;
  const [activeIndex, setActiveIndex] = useState(0);

  if (allImages.length === 0) {
    return (
      <div className="bb-gallery">
        <div className="bb-gallery__main bb-gallery__placeholder">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <circle cx="8.5" cy="8.5" r="1.5" />
            <path d="M21 15l-5-5L5 21" />
          </svg>
          <span>No images available</span>
        </div>
      </div>
    );
  }

  const current = allImages[activeIndex];

  return (
    <div className="bb-gallery">
      <div className="bb-gallery__main">
        <img
          src={current.large || current.original}
          alt={current.alt || current.title}
          className="bb-gallery__img"
        />
        {allImages.length > 1 && (
          <>
            <button
              className="bb-gallery__nav bb-gallery__nav--prev"
              onClick={() => setActiveIndex((i) => (i === 0 ? allImages.length - 1 : i - 1))}
              aria-label="Previous image"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="15 18 9 12 15 6" /></svg>
            </button>
            <button
              className="bb-gallery__nav bb-gallery__nav--next"
              onClick={() => setActiveIndex((i) => (i === allImages.length - 1 ? 0 : i + 1))}
              aria-label="Next image"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="9 18 15 12 9 6" /></svg>
            </button>
            <span className="bb-gallery__counter">{activeIndex + 1} / {allImages.length}</span>
          </>
        )}
      </div>
      {allImages.length > 1 && (
        <div className="bb-gallery__thumbs">
          {allImages.map((img, i) => (
            <button
              key={i}
              className={`bb-gallery__thumb ${i === activeIndex ? 'bb-gallery__thumb--active' : ''}`}
              onClick={() => setActiveIndex(i)}
              aria-label={`View image ${i + 1}`}
            >
              <img src={img.thumbnail} alt={img.alt || img.title} />
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
