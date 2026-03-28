import './app-download-section.css';

export function AppDownloadSection() {
  return (
    <section className="app-download-section section">
      <div className="container">
        <div className="app-download">
          <div className="app-download__content">
            <h2 className="app-download__title">Get Billoria App</h2>
            <p className="app-download__description">
              Browse billboards, manage bookings, and track campaigns on the go.
              Download our mobile app for the best experience.
            </p>
            <div className="app-download__buttons">
              <a href="#" className="app-download__button app-download__button--ios">
                <div className="app-download__button-icon">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                  </svg>
                </div>
                <div className="app-download__button-text">
                  <span className="app-download__button-subtitle">Download on the</span>
                  <span className="app-download__button-title">App Store</span>
                </div>
              </a>

              <a href="#" className="app-download__button app-download__button--android">
                <div className="app-download__button-icon">
                  <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.523 15.341c-1.08 0-1.95.87-1.95 1.95s.87 1.95 1.95 1.95c1.08 0 1.95-.87 1.95-1.95s-.87-1.95-1.95-1.95zm-11.046 0c-1.08 0-1.95.87-1.95 1.95s.87 1.95 1.95 1.95 1.95-.87 1.95-1.95-.87-1.95-1.95-1.95zm11.336-5.5l2.598-4.5c.14-.24.06-.55-.18-.69-.24-.14-.55-.06-.69.18L17 9.35C15.5 8.68 13.79 8.3 12 8.3s-3.5.38-5 1.05L4.45 4.85c-.14-.24-.46-.32-.69-.18-.24.14-.32.46-.18.69l2.58 4.5C2.5 11.78 0 15.76 0 20.3h24c0-4.54-2.5-8.52-6.187-10.46zM12 3.79c.5 0 .91-.41.91-.91 0-.5-.41-.91-.91-.91-.5 0-.91.41-.91.91 0 .5.41.91.91.91z"/>
                  </svg>
                </div>
                <div className="app-download__button-text">
                  <span className="app-download__button-subtitle">Get it on</span>
                  <span className="app-download__button-title">Google Play</span>
                </div>
              </a>
            </div>
          </div>
          <div className="app-download__image">
            <div className="app-download__phone-mockup">
              <div className="app-download__phone-screen">
                📱
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
