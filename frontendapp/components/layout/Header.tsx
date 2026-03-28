'use client';

import './header.css';
import { useEffect, useState } from 'react';

export function Header() {
  const [isScrolled, setIsScrolled] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      const scrollPosition = window.scrollY;
      setIsScrolled(scrollPosition > 50);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <header className={`site-header ${isScrolled ? 'is-scrolled' : ''}`}>
      <div className="container">
        <div className="site-header__content">
          {/* Logo */}
          <div className="site-header__logo">
            <a href="/" className="site-header__logo-link">
              <img 
                src="/billoria-logo-location.svg" 
                alt="Billoria - Billboard Marketplace" 
                className="site-header__logo-image"
              />
            </a>
          </div>

          {/* Actions - Right side */}
          <div className="site-header__actions">
            {/* Search Icon */}
            <button className="site-header__search-btn" aria-label="Search">
              <svg 
                className="site-header__icon" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path 
                  strokeLinecap="round" 
                  strokeLinejoin="round" 
                  strokeWidth={2} 
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" 
                />
              </svg>
            </button>

            {/* Mobile Menu Button */}
            <button className="site-header__nav-toggle" aria-label="Menu">
              <svg 
                className="site-header__icon" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path 
                  strokeLinecap="round" 
                  strokeLinejoin="round" 
                  strokeWidth={2} 
                  d="M4 6h16M4 12h16M4 18h16" 
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>
  );
}
