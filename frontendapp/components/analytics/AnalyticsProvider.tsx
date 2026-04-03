'use client';

import { useEffect } from 'react';
import { usePathname } from 'next/navigation';
import { getFirebaseAnalytics } from '@/lib/firebase/config';
import { trackPageView, setAnalyticsUserId } from '@/lib/firebase/analytics';
import { getSession } from '@/lib/api/auth';

/**
 * Component to initialize Firebase Analytics and track page views.
 * Add this to the root layout to enable analytics across the app.
 */
function AnalyticsProvider() {
  const pathname = usePathname();

  // Initialize analytics on mount
  useEffect(() => {
    const initAnalytics = async () => {
      const analytics = await getFirebaseAnalytics();
      if (analytics) {
        console.log('Firebase Analytics initialized');
        
        // Set user ID if logged in
        const session = getSession();
        if (session?.user?.uid) {
          await setAnalyticsUserId(session.user.uid);
        }
      }
    };

    initAnalytics();
  }, []);

  // Track page views on route change
  useEffect(() => {
    if (pathname) {
      trackPageView(pathname);
    }
  }, [pathname]);

  return null; // This component doesn't render anything
}

export { AnalyticsProvider };
