'use client';

import { useEffect, useRef } from 'react';
import './areas-carousel.css';

const tier1Areas = [
  {
    id: 1,
    name: 'Gulshan',
    district: 'Dhaka',
    image: 'https://images.unsplash.com/photo-1518732714860-b62714ce0c59?w=400&h=300&fit=crop',
    billboards: 45,
  },
  {
    id: 2,
    name: 'Banani',
    district: 'Dhaka',
    image: 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=400&h=300&fit=crop',
    billboards: 38,
  },
  {
    id: 3,
    name: 'Dhanmondi',
    district: 'Dhaka',
    image: 'https://images.unsplash.com/photo-1556115457-8fa0cfe38bfa?w=400&h=300&fit=crop',
    billboards: 52,
  },
  {
    id: 4,
    name: 'Mirpur',
    district: 'Dhaka',
    image: 'https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=400&h=300&fit=crop',
    billboards: 41,
  },
  {
    id: 5,
    name: 'Uttara',
    district: 'Dhaka',
    image: 'https://images.unsplash.com/photo-1516731566880-e1246f6a0836?w=400&h=300&fit=crop',
    billboards: 36,
  },
  {
    id: 6,
    name: 'Motijheel',
    district: 'Dhaka',
    image: 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=400&h=300&fit=crop',
    billboards: 29,
  },
];

export function AreasCarousel() {
  const scrollContainerRef = useRef<HTMLDivElement>(null);

  const scrollLeft = () => {
    if (scrollContainerRef.current) {
      scrollContainerRef.current.scrollBy({ left: -350, behavior: 'smooth' });
    }
  };

  const scrollRight = () => {
    if (scrollContainerRef.current) {
      scrollContainerRef.current.scrollBy({ left: 350, behavior: 'smooth' });
    }
  };

  return (
    <section className="areas-carousel-section section">
      <div className="container">
        <div className="areas-carousel__header">
          <div>
            <h2 className="areas-carousel__title">Tier 1 Premium Areas</h2>
            <p className="areas-carousel__subtitle">
              Explore high-traffic locations in Bangladesh&apos;s major cities
            </p>
          </div>
          <div className="areas-carousel__controls">
            <button
              onClick={scrollLeft}
              className="areas-carousel__button areas-carousel__button--prev"
              aria-label="Previous"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="15 18 9 12 15 6" /></svg>
            </button>
            <button
              onClick={scrollRight}
              className="areas-carousel__button areas-carousel__button--next"
              aria-label="Next"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="9 18 15 12 9 6" /></svg>
            </button>
          </div>
        </div>

        <div className="areas-carousel">
          <button
            onClick={scrollLeft}
            className="areas-carousel__button areas-carousel__button--prev areas-carousel__button--mobile"
            aria-label="Previous"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="15 18 9 12 15 6" /></svg>
          </button>
          <button
            onClick={scrollRight}
            className="areas-carousel__button areas-carousel__button--next areas-carousel__button--mobile"
            aria-label="Next"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="9 18 15 12 9 6" /></svg>
          </button>
          <div className="areas-carousel__container" ref={scrollContainerRef}>
            {tier1Areas.map((area) => (
              <article key={area.id} className="area-card">
                <div className="area-card__image">
                  <img src={area.image} alt={area.name} className="area-card__img" />
                  <div className="area-card__overlay">
                    <span className="area-card__count">{area.billboards} Billboards</span>
                  </div>
                </div>
                <div className="area-card__content">
                  <h3 className="area-card__name">{area.name}</h3>
                  <p className="area-card__district">{area.district}</p>
                  <button className="area-card__button">View Billboards</button>
                </div>
              </article>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
