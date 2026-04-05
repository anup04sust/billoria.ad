'use client';

import { useRef, useEffect } from 'react';
import { IconPlay } from '@/lib/icons/ui-icons';
import './billboard-video-section.css';

interface BillboardVideoSectionProps {
  title?: string;
  subtitle?: string;
}

export function BillboardVideoSection({
  title = 'Area Walkthrough',
  subtitle = 'Get a feel for the surroundings and foot traffic at this location',
}: BillboardVideoSectionProps) {
  const videoRef = useRef<HTMLVideoElement>(null);

  useEffect(() => {
    const video = videoRef.current;
    if (video) {
      video.muted = true;
      video.playsInline = true;
      video.play().catch(() => {
        // Autoplay blocked — play on user interaction
        const tryPlay = () => {
          video.play().catch(() => {});
          document.removeEventListener('click', tryPlay);
        };
        document.addEventListener('click', tryPlay, { once: true });
      });
    }
  }, []);

  return (
    <div className="bb-video-section">
      <div className="bb-video-section__header">
        <h2 className="bb-video-section__title">{title}</h2>
        <p className="bb-video-section__subtitle">{subtitle}</p>
      </div>

      <div className="bb-video-section__player">
        {/* Play icon overlay */}
        <div className="bb-video-section__play-hint" aria-hidden="true">
          <IconPlay />
        </div>

        <video
          ref={videoRef}
          className="bb-video-section__video"
          loop
          muted
          playsInline
          preload="metadata"
          poster="/videos/optimized/v1-poster.jpg"
        >
          <source src="/videos/optimized/v1-wm.webm" type="video/webm" />
          <source src="/videos/optimized/v1-wm.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>

        {/* Subtle gradient overlay for branding */}
        <div className="bb-video-section__overlay" />

        {/* Watermark */}
        <div className="bb-video-section__watermark" aria-hidden="true">
          <svg height="18" viewBox="0 0 370 60" xmlns="http://www.w3.org/2000/svg">
            <text x="68" y="42" fontFamily="Inter, sans-serif" fontWeight="800" fontSize="28" fill="white" opacity="0.6">BILLORIA <tspan fill="#C1121F" opacity="0.8">ADPOINT</tspan></text>
          </svg>
        </div>
      </div>
    </div>
  );
}
