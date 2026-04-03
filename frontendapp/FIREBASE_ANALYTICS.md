# Firebase Analytics Integration

## Overview

Firebase Analytics has been integrated into the Billoria.ad frontend to track user behavior, engagement, and key business metrics.

## Configuration

### Environment Variables

Added to [.env.local](../.env.local):

```env
NEXT_PUBLIC_FIREBASE_MEASUREMENT_ID=G-MR00MTX1K3
```

### Complete Firebase Config

```typescript
{
  apiKey: "AIzaSyAHvzAvThBsxI4crrKLo5Kfw7lTaR4z-S0",
  authDomain: "billoriaadpoint.firebaseapp.com",
  projectId: "billoriaadpoint",
  storageBucket: "billoriaadpoint.firebasestorage.app",
  messagingSenderId: "246037782925",
  appId: "1:246037782925:web:80d2f49523f71fab0d5dc4",
  measurementId: "G-MR00MTX1K3"
}
```

## Components

### 1. Analytics Configuration ([lib/firebase/config.ts](../lib/firebase/config.ts))

Core Firebase Analytics initialization:

```typescript
import { getAnalytics } from 'firebase/analytics';

// Initialize Analytics
const analytics = await getFirebaseAnalytics();
```

**Features:**
- Singleton pattern for Analytics instance
- Browser-only initialization (no SSR)
- Support detection for analytics
- Error handling and logging

### 2. Analytics Helpers ([lib/firebase/analytics.ts](../lib/firebase/analytics.ts))

Utility functions for tracking events:

```typescript
import { trackEvent, trackPageView, trackLogin } from '@/lib/firebase/analytics';

// Track custom event
await trackEvent('button_clicked', { button_id: 'cta_signup' });

// Track page view
await trackPageView('/billboard/123');

// Track login
await trackLogin('email', 'billboard_owner');
```

**Available Functions:**
- `trackEvent()` - Track custom events
- `trackPageView()` - Track page views
- `setAnalyticsUserId()` - Set user ID
- `setAnalyticsUserProperties()` - Set user properties
- `trackLogin()` - Track login events
- `trackSignup()` - Track signup events
- `trackBillboardView()` - Track billboard views
- `trackSearch()` - Track search queries
- `trackBookingRequest()` - Track booking requests
- `trackError()` - Track errors

### 3. Analytics Provider ([components/analytics/AnalyticsProvider.tsx](../components/analytics/AnalyticsProvider.tsx))

Auto-initializes analytics and tracks page views:

```typescript
<AnalyticsProvider />
```

**Features:**
- Auto-initializes on app load
- Sets user ID from session
- Tracks page views on route change
- Automatically integrated in root layout

### 4. Push Notification Analytics

Integrated in [PushNotificationPrompt.tsx](../components/notifications/PushNotificationPrompt.tsx):

**Events Tracked:**
- `notification_permission_granted` - User grants permission
- `notification_permission_denied` - User denies or dismisses
- Includes user_id and reason parameters

## Predefined Events

All event names are defined in `AnalyticsEvents` constant:

```typescript
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
};
```

## Usage Examples

### Track Billboard View

```typescript
import { trackBillboardView } from '@/lib/firebase/analytics';

// In billboard detail page
useEffect(() => {
  trackBillboardView(billboard.id, billboard.category);
}, [billboard.id]);
```

### Track Search

```typescript
import { trackSearch } from '@/lib/firebase/analytics';

const handleSearch = async (query: string) => {
  const results = await searchBillboards(query);
  trackSearch(query, results.length);
};
```

### Track Booking

```typescript
import { trackBookingRequest } from '@/lib/firebase/analytics';

const handleBooking = async (billboardId: string, amount: number) => {
  await createBooking(billboardId, amount);
  trackBookingRequest(billboardId, amount);
};
```

### Track User Login

```typescript
import { trackLogin, setAnalyticsUserId } from '@/lib/firebase/analytics';

const handleLogin = async (credentials) => {
  const user = await login(credentials);
  
  // Set user ID
  await setAnalyticsUserId(user.uid);
  
  // Track login event
  await trackLogin('email', user.accountType);
};
```

### Track Errors

```typescript
import { trackError } from '@/lib/firebase/analytics';

try {
  await riskyOperation();
} catch (error) {
  trackError('api_error', error.message);
  throw error;
}
```

## Auto-Tracked Events

The following events are automatically tracked:

1. **Page Views** - Every route change via `AnalyticsProvider`
2. **User ID** - Set automatically on login
3. **Notification Permissions** - Tracked in `PushNotificationPrompt`

## Viewing Analytics Data

### Firebase Console

1. Go to [Firebase Console](https://console.firebase.google.com)
2. Select **billoriaadpoint** project
3. Navigate to **Analytics** → **Dashboard**

### Available Reports

- **Events** - See all tracked events with parameters
- **User Properties** - View user demographics and properties
- **Realtime** - Monitor active users and events in real-time
- **Funnels** - Create conversion funnels (e.g., signup → booking)
- **Retention** - Track user retention over time
- **Engagement** - See user engagement metrics

### Custom Dashboards

Create custom reports for:
- Billboard view → booking conversion rate
- Search → billboard view rate
- Notification permission acceptance rate
- Payment completion rate
- Error frequency by type

## Privacy & Compliance

### GDPR Compliance

Analytics respects user privacy:
- No PII (Personally Identifiable Information) in event parameters
- User IDs are anonymized Firebase UIDs
- Users can opt-out via browser settings
- Cookie consent required (to be implemented)

### Data Retention

- Events: 14 months (default Firebase setting)
- User properties: Until manually deleted
- Can be configured in Firebase Console → Project Settings → Data Settings

## Testing

### Debug Mode

Enable analytics debugging:

```typescript
// In browser console
import { setAnalyticsCollectionEnabled } from 'firebase/analytics';
setAnalyticsCollectionEnabled(analytics, true);
```

Or add to URL:
```
https://billoria-ad.ddev.site:3001?debug_analytics=true
```

### DebugView

1. Visit Firebase Console → Analytics → DebugView
2. Enable debug mode in your browser
3. See events in real-time as you interact with the app

### Testing Checklist

- [ ] Page view tracked on route change
- [ ] User ID set after login
- [ ] Login/signup events fire correctly
- [ ] Billboard view events track billboard ID
- [ ] Search events include query and results count
- [ ] Notification permission events fire
- [ ] Booking events include amount and currency
- [ ] Error events captured with error type

## Future Enhancements

1. **Enhanced E-commerce Tracking**
   - Track add_to_cart, begin_checkout, purchase
   - Revenue attribution

2. **User Engagement Score**
   - Calculate engagement score based on interactions
   - Set as user property

3. **A/B Testing Integration**
   - Firebase Remote Config + Analytics
   - Test different UI variants

4. **Predictive Analytics**
   - Predict user churn
   - Identify high-value users

5. **Custom Audiences**
   - Create remarketing audiences
   - Export to Google Ads

## Troubleshooting

### Analytics Not Tracking

1. **Check browser console** for errors
2. **Verify environment variables** are set correctly
3. **Check Firebase project** is active
4. **Disable ad blockers** (they may block analytics)
5. **Check DebugView** in Firebase Console

### Events Not Appearing

- Events can take 24-48 hours to appear in standard reports
- Use DebugView for real-time validation
- Check event names match predefined constants

### User Properties Not Set

```typescript
// Ensure user properties are set after login
await setAnalyticsUserProperties({
  account_type: user.accountType,
  verified: user.isVerified ? 'yes' : 'no',
});
```

## Related Documentation

- [Firebase Analytics Docs](https://firebase.google.com/docs/analytics)
- [Firebase Console](https://console.firebase.google.com/project/billoriaadpoint/analytics)
- [lib/firebase/config.ts](../lib/firebase/config.ts) - Configuration
- [lib/firebase/analytics.ts](../lib/firebase/analytics.ts) - Helper functions
- [components/analytics/AnalyticsProvider.tsx](../components/analytics/AnalyticsProvider.tsx) - Provider component
