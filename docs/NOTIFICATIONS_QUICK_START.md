# Billoria Notifications - Quick Start Guide

Complete notification system with in-app notifications and Firebase Cloud Messaging push notifications.

## ✅ Module Status

**Installed**: billoria_notifications  
**Database Tables**: 
- `billoria_notifications` - In-app notifications
- `billoria_fcm_tokens` - Device tokens for push notifications

**Test Results**: All tests passing ✓

## 1. Firebase Setup (Required for Push Notifications)

### Get Firebase Credentials

⚠️ **IMPORTANT**: Firebase has deprecated the Legacy API. You need to enable it first!

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create or select your project
3. Navigate to: **Project Settings** → **Cloud Messaging**
4. **Enable Legacy API**: Click 3-dot menu → "Enable Cloud Messaging API (Legacy)"
5. Copy the **Server Key** (Legacy)

📘 **For Production**: Consider migrating to HTTP v1 API - see [FCM_V1_MIGRATION.md](../application-wiki/FCM_V1_MIGRATION.md)

### Configure Backend

```bash
cd /var/www/billoria.ad/cmsapi
ddev drush config:set billoria_notifications.firebase server_key "YOUR_SERVER_KEY_HERE" -y
ddev drush cr
```

## 2. Backend Usage

### Create Notification (Auto-sends Push)

```php
$notification_manager = \Drupal::service('billoria_notifications.manager');

$nid = $notification_manager->createNotification(
  uid: 5,                          // User to notify
  type: 'booking',                 // Notification type
  title: 'Booking Approved',       // Title  
  message: 'Your booking #123 has been approved.',
  metadata: [                      // Optional metadata
    'billboard_id' => 123,
    'booking_id' => 456,
    'action_url' => '/dashboard/bookings/456',
  ],
  priority: 'high',                // low, normal, high, urgent
  expires_at: strtotime('+7 days'), // Optional expiration
  send_push: true                  // Auto-send push (default: true)
);
```

### Notification Types

- `booking` - Booking requests, approvals, rejections
- `verification` - KYC/document verification updates
- `payment` - Payment confirmations, reminders
- `system` - Platform announcements
- `message` - Direct messages
- `alert` - Urgent notifications

## 3. REST API Endpoints

### In-App Notifications

```bash
# List notifications
GET /api/v1/notifications?limit=50&unread_only=true

# Get unread count
GET /api/v1/notifications/unread-count

# Mark as read
POST /api/v1/notifications/{nid}/mark-read

# Mark all as read
POST /api/v1/notifications/mark-all-read

# Delete notification
DELETE /api/v1/notifications/{nid}
```

### FCM Push Notifications

```bash
# Register device token
POST /api/v1/notifications/fcm/register
Body: {"token": "fcm-token", "deviceType": "web", "deviceName": "Chrome"}

# Unregister device token
POST /api/v1/notifications/fcm/unregister
Body: {"token": "fcm-token"}

# List registered devices  
GET /api/v1/notifications/fcm/tokens
```

## 4. Frontend Integration (Next.js)

### Install Firebase

```bash
cd /var/www/billoria.ad/frontendapp
pnpm add firebase
```

### Environment Variables (`.env.local`)

```env
# Firebase Configuration
NEXT_PUBLIC_FIREBASE_API_KEY=your-api-key
NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
NEXT_PUBLIC_FIREBASE_PROJECT_ID=your-project-id
NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID=123456789
NEXT_PUBLIC_FIREBASE_APP_ID=1:123456789:web:abcdef
NEXT_PUBLIC_FIREBASE_VAPID_KEY=your-vapid-key
```

### Firebase Config (`lib/firebase/config.ts`)

```typescript
import { initializeApp } from 'firebase/app';
import { getMessaging } from 'firebase/messaging';

const firebaseConfig = {
  apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
  authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN,
  projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID,
  storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID,
  appId: process.env.NEXT_PUBLIC_FIREBASE_APP_ID,
};

const app = initializeApp(firebaseConfig);
export const messaging = typeof window !== 'undefined' ? getMessaging(app) : null;
```

### API Client (`lib/api/fcmAPI.ts`)

```typescript
import { apiClient } from './apiClient';

export const fcmAPI = {
  registerToken: async (data: { token: string; deviceType: string; deviceName?: string }) => {
    return apiClient.post('/api/v1/notifications/fcm/register', data);
  },
  
  unregisterToken: async (token: string) => {
    return apiClient.post('/api/v1/notifications/fcm/unregister', { token });
  },
  
  listTokens: async () => {
    return apiClient.get('/api/v1/notifications/fcm/tokens');
  },
};

export const notificationAPI = {
  list: async (params = {}) => {
    const query = new URLSearchParams(params).toString();
    return apiClient.get(`/api/v1/notifications?${query}`);
  },
  
  unreadCount: async () => {
    return apiClient.get('/api/v1/notifications/unread-count');
  },
  
  markAsRead: async (nid: number) => {
    return apiClient.post(`/api/v1/notifications/${nid}/mark-read`);
  },
  
  markAllAsRead: async () => {
    return apiClient.post('/api/v1/notifications/mark-all-read');
  },
  
  delete: async (nid: number) => {
    return apiClient.delete(`/api/v1/notifications/${nid}`);
  },
};
```

### Service Worker (`public/firebase-messaging-sw.js`)

```javascript
importScripts('https://www.gstatic.com/firebasejs/10.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.0.0/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: "your-api-key",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:web:abcdef"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/icon-192x192.png',
    data: payload.data,
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
```

## 5. Testing

### Backend Test

```bash
cd /var/www/billoria.ad/cmsapi
ddev drush scr scripts/test-notifications.php
```

**Expected Output:**
- ✓ Create notifications
- ✓ List notifications
- ✓ Mark as read
- ✓ Delete notifications
- ✓ Register/unregister FCM tokens

### Manual API Test

```bash
# Test unread count (requires authentication)
curl -X GET "https://billoria-ad-api.ddev.site/api/v1/notifications/unread-count" \
  -H "Cookie: SESS_ID=your-session-cookie"
```

## 6. Common Use Cases

### Booking Approval Notification

```php
$notification_manager->createNotification(
  uid: $booking->getOwnerId(),
  type: 'booking',
  title: 'Booking Approved',
  message: sprintf('Your booking for "%s" has been approved.', $billboard->label()),
  metadata: [
    'billboard_id' => $billboard->id(),
    'booking_id' => $booking->id(),
    'action_url' => '/dashboard/bookings/' . $booking->id(),
  ],
  priority: 'high'
);
```

### KYC Verification Complete

```php
$notification_manager->createNotification(
  uid: $user->id(),
  type: 'verification',
  title: 'Verification Complete',
  message: 'Your KYC verification has been approved.',
  metadata: ['action_url' => '/dashboard/billboards/create'],
  priority: 'high'
);
```

### Payment Reminder

```php
$notification_manager->createNotification(
  uid: $user->id(),
  type: 'payment',
  title: 'Payment Due Soon',
  message: 'Your payment is due in 2 days.',
  metadata: [
    'booking_id' => $booking->id(),
    'amount' => 50000,
    'currency' => 'BDT',
  ],
  priority: 'urgent',
  expires_at: strtotime('+3 days')
);
```

## 7. Maintenance

### Cron Tasks (Automatic)

The module automatically runs cleanup during cron:
- Deletes expired notifications
- Removes inactive FCM tokens (30+ days old)

```bash
# Run manually
ddev drush cron
```

### View Logs

```bash
# Backend logs
cd /var/www/billoria.ad/cmsapi
ddev drush watchdog:show --type=billoria_notifications

# DDEV logs
ddev logs | grep billoria
```

## 📚 Documentation

- [README.md](cmsapi/web/modules/custom/billoria_notifications/README.md) - Module documentation
- [NOTIFICATIONS_API.md](application-wiki/NOTIFICATIONS_API.md) - API reference
- [FCM_PUSH_NOTIFICATIONS.md](application-wiki/FCM_PUSH_NOTIFICATIONS.md) - Firebase setup guide

## 🚀 Production Deployment

Before going live:

1. ✅ Configure Firebase Server Key
2. ✅ Set up frontend Firebase credentials
3. ✅ Test push notifications on all platforms (web, Android, iOS)
4. ✅ Configure cron for automatic cleanup
5. ✅ Set up monitoring/logging
6. ✅ Test notification delivery rates

## Support

For issues or questions:
- Check logs: `ddev drush watchdog:show --type=billoria_notifications`
- Review Firebase Console for delivery stats
- Test endpoints with authentication tokens
