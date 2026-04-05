'use client';

import { useState } from 'react';
import { IconImagePlaceholder, IconChevronLeft, IconChevronRight } from '@/lib/icons/ui-icons';
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
          <IconImagePlaceholder />
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
              <IconChevronLeft />
            </button>
            <button
              className="bb-gallery__nav bb-gallery__nav--next"
              onClick={() => setActiveIndex((i) => (i === allImages.length - 1 ? 0 : i + 1))}
              aria-label="Next image"
            >
              <IconChevronRight />
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
