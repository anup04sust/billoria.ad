'use client';

import './services-section.css';
import { useEffect, useRef } from 'react';

const services = [
  {
    id: 1,
    number: '01',
    title: 'Billboard Discovery',
    description: 'Find the perfect outdoor advertising space across Bangladesh. Search by location, size, traffic density, and budget range with real-time availability.',
    features: [
      'City and highway-based search',
      'LED and static billboard filtering',
      'Traffic density insights',
      'Budget-friendly recommendations',
    ],
    featured: false,
  },
  {
    id: 2,
    number: '02',
    title: 'Campaign Planning',
    description: 'Strategic OOH campaign planning backed by data-driven insights. From single boards to nationwide rollouts, we help you maximize impact.',
    features: [
      'Location recommendations',
      'Multi-location campaign planning',
      'Launch and seasonal packages',
      'City takeover strategies',
    ],
    featured: true,
  },
  {
    id: 3,
    number: '03',
    title: 'Creative Support',
    description: 'Professional billboard design and adaptation services. From concept to LED-ready creatives, we ensure your message stands out.',
    features: [
      'Billboard artwork design',
      'Size adaptation for multiple formats',
      'LED and digital screen optimization',
      'Bengali and English copywriting',
    ],
    featured: false,
  },
  {
    id: 4,
    number: '04',
    title: 'Booking Management',
    description: 'Seamless booking workflow from inquiry to installation. Track availability, request quotes, and manage your entire campaign lifecycle.',
    features: [
      'Real-time availability status',
      'Quote request and comparison',
      'Multi-location booking',
      'Campaign dashboard and tracking',
    ],
    featured: false,
  },
  {
    id: 5,
    number: '05',
    title: 'Location Analytics',
    description: 'Data-enriched billboard profiles with visibility scoring, audience demographics, and traffic patterns. Make informed placement decisions.',
    features: [
      'Traffic pattern analysis',
      'Audience demographic insights',
      'Visibility scoring system',
      'Landmark proximity mapping',
    ],
    featured: false,
  },
  {
    id: 6,
    number: '06',
    title: 'Partner Network',
    description: 'Connect with verified media owners, agencies, and production vendors. Access our ecosystem of trusted outdoor advertising professionals.',
    features: [
      'Verified agency partnerships',
      'Media owner network',
      'Production vendor directory',
      'Installation coordination',
    ],
    featured: false,
  },
];

export function ServicesSection() {
  const videoRef = useRef<HTMLVideoElement>(null);

  useEffect(() => {
    const video = videoRef.current;
    if (video) {
      // Ensure video is muted before trying to play
      video.muted = true;
      video.playsInline = true;
      
      // Try to play the video
      const playPromise = video.play();
      
      if (playPromise !== undefined) {
        playPromise
          .then(() => {
            console.log('Video playing successfully');
          })
          .catch((error) => {
            console.error('Video autoplay failed:', error);
            // Try again on user interaction
            document.addEventListener('click', () => {
              video.play().catch(e => console.error('Play on click failed:', e));
            }, { once: true });
          });
      }
    }
  }, []);

  return (
    <section className="services-section section">
      {/* Video Background */}
      <video
        ref={videoRef}
        className="services-section__video"
        autoPlay
        loop
        muted
        playsInline
        preload="auto"
        controls={false}
      >
        {/* WebM for Firefox, Chrome, Opera */}
        <source src="/service-bg.webm" type="video/webm" />
        {/* MP4 for Safari and older browsers */}
        <source src="/service-bg.mp4" type="video/mp4" />
        Your browser does not support the video tag.
      </video>
      
      {/* Overlay with blur */}
      <div className="services-section__overlay" />
      
      <div className="services-section__header">
        <h2 className="services-section__title">Complete Outdoor Advertising Solutions</h2>
        <p className="services-section__subtitle">
          From billboard discovery to campaign execution — everything you need for successful OOH advertising
        </p>
      </div>
      
      <div className="services-grid">
        {services.map((service) => (
          <article
            key={service.id}
            className={`service-card ${service.featured ? 'service-card--featured' : ''}`}
            tabIndex={0}
          >
            <div className="service-card__header">
              <h3 className="service-card__title">{service.title}</h3>
              <span className="service-card__number">/{service.number}</span>
            </div>
            
            <div className="service-card__content">
              <p className="service-card__description">{service.description}</p>
              <ul className="service-card__features">
                {service.features?.map((feature, index) => (
                  <li key={index} className="service-card__feature">
                    {feature}
                  </li>
                ))}
              </ul>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}
