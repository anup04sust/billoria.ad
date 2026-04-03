import { logEvent, setUserProperties, setUserId } from 'firebase/analytics';
import { getFirebaseAnalytics } from './config';

/**
 * Analytics event names for Billoria platform.
 */
export const AnalyticsEvents = {
  // User events
  USER_LOGIN: 'user_login',
  USER_SIGNUP: 'user_signup',
  USER_LOGOUT: 'user_logout',
  
  // Billboard events
  BILLBOARD_VIEW: 'billboard_view',
  BILLBOARD_SEARCH: 'billboard_search',
  BILLBOARD_FAVORITE: 'billboard_favorite',
  
  // Booking events
  BOOKING_REQUEST: 'booking_request',
  BOOKING_CONFIRMED: 'booking_confirmed',
  BOOKING_CANCELLED: 'booking_cancelled',
  
  // Payment events
  PAYMENT_INITIATED: 'payment_initiated',
  PAYMENT_COMPLETED: 'payment_completed',
  PAYMENT_FAILED: 'payment_failed',
  
  // Notification events
  NOTIFICATION_RECEIVED: 'notification_received',
  NOTIFICATION_CLICKED: 'notification_clicked',
  NOTIFICATION_PERMISSION_GRANTED: 'notification_permission_granted',
  NOTIFICATION_PERMISSION_DENIED: 'notification_permission_denied',
  
  // Dashboard events
  DASHBOARD_VIEW: 'dashboard_view',
  PROFILE_UPDATED: 'profile_updated',
  
  // Error events
  ERROR_OCCURRED: 'error_occurred',
} as const;

/**
 * Track a custom event.
 * 
 * @param eventName - Name of the event
 * @param params - Optional event parameters
 */
export async function trackEvent(
  eventName: string,
  params?: Record<string, string | number | boolean>
) {
  try {
    const analytics = await getFirebaseAnalytics();
    if (!analytics) return;
    
    logEvent(analytics, eventName, params);
  } catch (error) {
    console.error('Analytics tracking error:', error);
  }
}

/**
 * Track page view.
 * 
 * @param pagePath - The page path
 * @param pageTitle - Optional page title
 */
export async function trackPageView(pagePath: string, pageTitle?: string) {
  await trackEvent('page_view', {
    page_path: pagePath,
    page_title: pageTitle || document.title,
  });
}

/**
 * Set user ID for analytics.
 * 
 * @param userId - The user's unique ID
 */
export async function setAnalyticsUserId(userId: string | null) {
  try {
    const analytics = await getFirebaseAnalytics();
    if (!analytics) return;
    
    if (userId) {
      setUserId(analytics, userId);
    } else {
      setUserId(analytics, null);
    }
  } catch (error) {
    console.error('Analytics setUserId error:', error);
  }
}

/**
 * Set user properties for analytics.
 * 
 * @param properties - User properties to set
 */
export async function setAnalyticsUserProperties(
  properties: Record<string, string>
) {
  try {
    const analytics = await getFirebaseAnalytics();
    if (!analytics) return;
    
    setUserProperties(analytics, properties);
  } catch (error) {
    console.error('Analytics setUserProperties error:', error);
  }
}

/**
 * Track user login event.
 * 
 * @param method - Login method (e.g., 'email', 'google', 'facebook')
 * @param accountType - User's account type
 */
export async function trackLogin(method: string, accountType?: string) {
  await trackEvent(AnalyticsEvents.USER_LOGIN, {
    method,
    ...(accountType && { account_type: accountType }),
  });
}

/**
 * Track user signup event.
 * 
 * @param method - Signup method
 * @param accountType - User's account type
 */
export async function trackSignup(method: string, accountType: string) {
  await trackEvent(AnalyticsEvents.USER_SIGNUP, {
    method,
    account_type: accountType,
  });
}

/**
 * Track billboard view event.
 * 
 * @param billboardId - Billboard ID
 * @param category - Billboard category
 */
export async function trackBillboardView(
  billboardId: string,
  category?: string
) {
  await trackEvent(AnalyticsEvents.BILLBOARD_VIEW, {
    billboard_id: billboardId,
    ...(category && { category }),
  });
}

/**
 * Track search event.
 * 
 * @param searchTerm - The search query
 * @param resultsCount - Number of results
 */
export async function trackSearch(searchTerm: string, resultsCount: number) {
  await trackEvent(AnalyticsEvents.BILLBOARD_SEARCH, {
    search_term: searchTerm,
    results_count: resultsCount,
  });
}

/**
 * Track booking request event.
 * 
 * @param billboardId - Billboard ID
 * @param amount - Booking amount
 */
export async function trackBookingRequest(
  billboardId: string,
  amount: number
) {
  await trackEvent(AnalyticsEvents.BOOKING_REQUEST, {
    billboard_id: billboardId,
    value: amount,
    currency: 'BDT',
  });
}

/**
 * Track error event.
 * 
 * @param errorType - Type of error
 * @param errorMessage - Error message
 */
export async function trackError(errorType: string, errorMessage: string) {
  await trackEvent(AnalyticsEvents.ERROR_OCCURRED, {
    error_type: errorType,
    error_message: errorMessage.substring(0, 100), // Limit message length
  });
}
