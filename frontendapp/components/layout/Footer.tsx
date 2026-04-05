import './footer.css';
import { IconFacebook, IconTwitter, IconLinkedIn, IconInstagram } from '@/lib/icons/ui-icons';

export function Footer() {
  return (
    <footer className="site-footer">
      {/* Footer Widgets */}
      <div className="site-footer__widgets">
        <div className="container">
          <div className="site-footer__grid">
            {/* Company Info */}
            <div className="footer-widget">
              <h3 className="footer-widget__title">Billoria</h3>
              <p className="footer-widget__text">
                Bangladesh's leading billboard advertising platform. Connecting brands with premium outdoor advertising spaces.
              </p>
              <div className="footer-widget__social">
                <a href="#" className="footer-social-link" aria-label="Facebook">
                  <IconFacebook />
                </a>
                <a href="#" className="footer-social-link" aria-label="Twitter">
                  <IconTwitter />
                </a>
                <a href="#" className="footer-social-link" aria-label="LinkedIn">
                  <IconLinkedIn />
                </a>
                <a href="#" className="footer-social-link" aria-label="Instagram">
                  <IconInstagram />
                </a>
              </div>
            </div>

            {/* Quick Links */}
            <div className="footer-widget">
              <h3 className="footer-widget__title">Quick Links</h3>
              <ul className="footer-widget__list">
                <li><a href="/billboards" className="footer-widget__link">Browse Billboards</a></li>
                <li><a href="/locations" className="footer-widget__link">Locations</a></li>
                <li><a href="/pricing" className="footer-widget__link">Pricing</a></li>
                <li><a href="/how-it-works" className="footer-widget__link">How It Works</a></li>
                <li><a href="/success-stories" className="footer-widget__link">Success Stories</a></li>
              </ul>
            </div>

            {/* For Businesses */}
            <div className="footer-widget">
              <h3 className="footer-widget__title">For Businesses</h3>
              <ul className="footer-widget__list">
                <li><a href="/brands" className="footer-widget__link">For Brands</a></li>
                <li><a href="/agencies" className="footer-widget__link">For Agencies</a></li>
                <li><a href="/owners" className="footer-widget__link">For Billboard Owners</a></li>
                <li><a href="/analytics" className="footer-widget__link">Analytics & Insights</a></li>
                <li><a href="/api" className="footer-widget__link">API Documentation</a></li>
              </ul>
            </div>

            {/* Support */}
            <div className="footer-widget">
              <h3 className="footer-widget__title">Support</h3>
              <ul className="footer-widget__list">
                <li><a href="/help" className="footer-widget__link">Help Center</a></li>
                <li><a href="/contact" className="footer-widget__link">Contact Us</a></li>
                <li><a href="/faq" className="footer-widget__link">FAQ</a></li>
                <li><a href="/terms" className="footer-widget__link">Terms of Service</a></li>
                <li><a href="/privacy" className="footer-widget__link">Privacy Policy</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      {/* Copyright Bar */}
      <div className="site-footer__copyright">
        <div className="container">
          <div className="site-footer__copyright-content">
            <p className="site-footer__copyright-text">
              © 2026 Billoria. All rights reserved.
            </p>
            <p className="site-footer__copyright-text">
              Made with ❤️ in Bangladesh
            </p>
          </div>
        </div>
      </div>
    </footer>
  );
}
