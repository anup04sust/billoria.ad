'use client';

import { useEffect, useState } from 'react';
import { usePushNotifications } from '@/lib/hooks/usePushNotifications';
import { getSession } from '@/lib/api/auth';
import { trackEvent, AnalyticsEvents } from '@/lib/firebase/analytics';
import { IconBell, IconBellOff, IconCheck, IconSpinner } from '@/lib/icons/ui-icons';
import './push-notification-prompt.css';

/**
 * Component that prompts logged-in users to enable push notifications.
 * 
 * Features:
 * - Auto-shows prompt for logged-in users who haven't enabled notifications
 * - Can be dismissed (stores preference in localStorage)
 * - Shows current notification status
 * - Allows users to enable/disable notifications
 */
export function PushNotificationPrompt() {
  const [userId, setUserId] = useState<string | null>(null);
  const [isDismissed, setIsDismissed] = useState(false);
  const [showPrompt, setShowPrompt] = useState(false);

  const {
    supported,
    permission,
    token,
    isLoading,
    error,
    requestPermission,
    unregister,
  } = usePushNotifications(userId);

  // Check login status
  useEffect(() => {
    const session = getSession();
    setUserId(session?.user?.uid || null);
    
    // Check if user previously dismissed the prompt
    const dismissed = localStorage.getItem('billoria_push_dismissed');
    setIsDismissed(dismissed === 'true');
  }, []);

  // Show prompt after component mounts (avoid hydration issues)
  useEffect(() => {
    const timer = setTimeout(() => {
      setShowPrompt(true);
    }, 60000); // Delay 1 minute after page load

    return () => clearTimeout(timer);
  }, []);

  // Don't render if:
  // - Not supported
  // - User not logged in
  // - Already granted permission
  // - User dismissed the prompt
  // - Still loading
  if (!supported || !userId || permission === 'granted' || isDismissed || !showPrompt) {
    return null;
  }

  const handleEnable = async () => {
    const success = await requestPermission();
    if (success) {
      console.log('Push notifications enabled successfully');
      // Track analytics event
      await trackEvent(AnalyticsEvents.NOTIFICATION_PERMISSION_GRANTED, {
        user_id: userId || 'unknown',
      });
    } else {
      // Track denial
      await trackEvent(AnalyticsEvents.NOTIFICATION_PERMISSION_DENIED, {
        user_id: userId || 'unknown',
      });
    }
  };

  const handleDismiss = () => {
    localStorage.setItem('billoria_push_dismissed', 'true');
    setIsDismissed(true);
    // Track dismissal
    trackEvent(AnalyticsEvents.NOTIFICATION_PERMISSION_DENIED, {
      user_id: userId || 'unknown',
      reason: 'dismissed',
    });
  };

  const handleDisable = async () => {
    await unregister();
    // Track disable
    await trackEvent(AnalyticsEvents.NOTIFICATION_PERMISSION_DENIED, {
      user_id: userId || 'unknown',
      reason: 'disabled',
    });
  };

  // If already enabled, show a small status indicator instead of full prompt
  if (token) {
    return (
      <div className="pnp-badge">
        <div className="pnp-badge__icon">
          <IconBell />
        </div>
        <span className="pnp-badge__text">Notifications enabled</span>
        <button className="pnp-badge__close" onClick={handleDisable} aria-label="Disable notifications">
          <IconBellOff />
        </button>
      </div>
    );
  }

  return (
    <>
      {/* Modal Backdrop */}
      <div className="pnp-overlay" onClick={handleDismiss} />
      
      {/* Modal Content */}
      <div className="pnp-modal-wrap">
        <div className="pnp-modal">
          {/* Header with gradient */}
          <div className="pnp-header">
            <div className="pnp-header__icon">
              <IconBell />
            </div>
            <h2 className="pnp-header__title">Enable Notifications</h2>
            <p className="pnp-header__subtitle">Stay updated with your bookings and payments</p>
          </div>

          {/* Body */}
          <div className="pnp-body">
            <div className="pnp-list">
              <div className="pnp-list__item">
                <div className="pnp-list__icon pnp-list__icon--blue">
                  <IconCheck />
                </div>
                <div className="pnp-list__content">
                  <h3 className="pnp-list__title">Booking Updates</h3>
                  <p className="pnp-list__desc">Get notified when your bookings are confirmed or updated</p>
                </div>
              </div>

              <div className="pnp-list__item">
                <div className="pnp-list__icon pnp-list__icon--green">
                  <IconCheck />
                </div>
                <div className="pnp-list__content">
                  <h3 className="pnp-list__title">Payment Alerts</h3>
                  <p className="pnp-list__desc">Receive instant alerts for payments and invoices</p>
                </div>
              </div>

              <div className="pnp-list__item">
                <div className="pnp-list__icon pnp-list__icon--purple">
                  <IconCheck />
                </div>
                <div className="pnp-list__content">
                  <h3 className="pnp-list__title">Important Announcements</h3>
                  <p className="pnp-list__desc">Never miss important platform updates</p>
                </div>
              </div>
            </div>

            {error && (
              <div className="pnp-error">{error}</div>
            )}

            {/* Buttons */}
            <div className="pnp-actions">
              <button
                onClick={handleEnable}
                disabled={isLoading}
                className="pnp-btn pnp-btn--primary"
              >
                {isLoading ? (
                  <>
                    <IconSpinner />
                    Enabling...
                  </>
                ) : (
                  'Allow Notifications'
                )}
              </button>
              <button onClick={handleDismiss} className="pnp-btn pnp-btn--secondary">
                Not Now
              </button>
            </div>

            <p className="pnp-footer">You can disable this anytime from settings</p>
          </div>
        </div>
      </div>
    </>
  );
}
