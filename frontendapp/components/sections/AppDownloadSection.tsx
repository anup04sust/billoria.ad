import { IconApple, IconGooglePlay } from '@/lib/icons/ui-icons';
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
                  <IconApple />
                </div>
                <div className="app-download__button-text">
                  <span className="app-download__button-subtitle">Download on the</span>
                  <span className="app-download__button-title">App Store</span>
                </div>
              </a>

              <a href="#" className="app-download__button app-download__button--android">
                <div className="app-download__button-icon">
                  <IconGooglePlay />
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
