# Push Notifications for Logged-in Users

## Quick Start Example

To add push notifications to your Next.js app, edit your layout:

```tsx
// app/layout.tsx
import { PushNotificationPrompt } from '@/components/notifications/PushNotificationPrompt';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html>
      <body>
        {children}
        
        {/* Auto-prompt logged-in users to enable notifications */}
        <PushNotificationPrompt />
      </body>
    </html>
  );
}
```

## What This Does

When a user logs in:
1. **Auto-detects login state** - Uses `getSession()` from auth.ts
2. **Shows notification prompt** - Non-intrusive bottom-right card (2 seconds after page load)
3. **Requests permission** - Browser native permission dialog
4. **Gets FCM token** - From Firebase Cloud Messaging
5. **Registers with backend** - Sends token to Drupal `/api/v1/fcm/tokens/register`
6. **Receives push notifications** - Real-time alerts even when browser is in background

## Features

### PushNotificationPrompt Component
- ✅ Only shows for logged-in users
- ✅ Auto-hides after permission granted
- ✅ Can be dismissed (stored in localStorage)
- ✅ Shows helpful message about what notifications include
- ✅ Handles errors gracefully

### usePushNotifications Hook
- ✅ Checks browser support
- ✅ Manages notification permission state
- ✅ Registers/unregisters FCM tokens
- ✅ Handles foreground messages
- ✅ Service worker for background notifications

### NotificationCenter Component
- ✅ Bell icon with unread count badge
- ✅ Dropdown list of recent notifications
- ✅ Marks notifications as read when clicked
- ✅ Auto-refreshes every minute
- ✅ Styled for dark mode

## Configuration Required

### 1. Firebase Setup

Edit `/var/www/billoria.ad/frontendapp/.env.local`:

```env
# Get these from Firebase Console → Project Settings
NEXT_PUBLIC_FIREBASE_API_KEY=AIza...
NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
NEXT_PUBLIC_FIREBASE_PROJECT_ID=your-project-id
NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID=246037782925
NEXT_PUBLIC_FIREBASE_APP_ID=1:246037782925:web:...

# Get this from Cloud Messaging → Web Push certificates
NEXT_PUBLIC_FIREBASE_VAPID_KEY=BNdF...
```

### 2. Backend Configuration

Drupal backend must have:
- ✅ `billoria_notifications` module enabled
- ✅ Firebase Service Account JSON configured at `/admin/config/billoria/notifications`
- ✅ V1 API enabled (not Legacy API)

See [FIREBASE_V1_SETUP_GUIDE.md](../../docs/FIREBASE_V1_SETUP_GUIDE.md) for backend setup.

## Usage Examples

### Example 1: Auto-prompt in Layout (Recommended)

```tsx
// app/layout.tsx
import { PushNotificationPrompt } from '@/components/notifications/PushNotificationPrompt';
import { NotificationCenter } from '@/components/notifications/NotificationCenter';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html>
      <body>
        <nav>
          {/* Notification bell icon in navbar */}
          <NotificationCenter />
        </nav>
        
        {children}
        
        {/* Auto-prompt for push notifications */}
        <PushNotificationPrompt />
      </body>
    </html>
  );
}
```

### Example 2: Manual Control

```tsx
'use client';

import { useEffect, useState } from 'react';
import { usePushNotifications } from '@/lib/hooks/usePushNotifications';
import { getSession } from '@/lib/api/auth';

export function NotificationSettings() {
  const [userId, setUserId] = useState<string | null>(null);
  
  useEffect(() => {
    const session = getSession();
    setUserId(session?.user?.uid || null);
  }, []);
  
  const {
    supported,
    permission,
    token,
    isLoading,
    requestPermission,
    unregister,
  } = usePushNotifications(userId);
  
  if (!supported) {
    return <p>Push notifications not supported</p>;
  }
  
  if (permission === 'granted' && token) {
    return (
      <div>
        <p>✓ Notifications enabled</p>
        <button onClick={unregister}>Disable</button>
      </div>
    );
  }
  
  return (
    <button onClick={requestPermission} disabled={isLoading}>
      {isLoading ? 'Enabling...' : 'Enable Push Notifications'}
    </button>
  );
}
```

### Example 3: Check Notification Support

```tsx
'use client';

import { useEffect, useState } from 'react';
import { isFirebaseConfigured } from '@/lib/firebase/config';

export function NotificationStatus() {
  const [status, setStatus] = useState<string>('checking...');
  
  useEffect(() => {
    if (typeof window === 'undefined') return;
    
    if (!isFirebaseConfigured()) {
      setStatus('Firebase not configured');
    } else if (!('Notification' in window)) {
      setStatus('Browser does not support notifications');
    } else if (Notification.permission === 'granted') {
      setStatus('Notifications enabled');
    } else if (Notification.permission === 'denied') {
      setStatus('Notifications blocked');
    } else {
      setStatus('Notifications not requested');
    }
  }, []);
  
  return <span>{status}</span>;
}
```

## How It Works

### Flow Diagram

```
User logs in
    ↓
PushNotificationPrompt renders (if not dismissed)
    ↓
User clicks "Enable Notifications"
    ↓
usePushNotifications hook
    ├─ Checks browser support
    ├─ Requests Notification.permission
    ├─ Gets FCM token from Firebase
    ├─ Registers token with Drupal backend
    └─ Sets up message listeners
    ↓
Backend can now send push notifications
    ↓
User receives notifications:
    ├─ Foreground: onMessage() → shows notification
    └─ Background: Service Worker → shows notification
```

### Service Worker

The service worker (`public/firebase-messaging-sw.js`) handles background notifications:
- Receives push messages when browser tab is closed
- Shows notification with custom title/body/icon
- Handles notification clicks (opens/focuses app)
- Auto-registered on component mount

## Testing

### 1. Start Dev Server

```bash
cd /var/www/billoria.ad/frontendapp
pnpm dev
```

### 2. Login to Frontend

Open https://billoria-ad.ddev.site:3000 (or localhost:3000) and login.

### 3. Enable Notifications

Click "Enable Notifications" when prompted.

### 4. Send Test Notification

```bash
cd /var/www/billoria.ad/cmsapi
ddev drush scr scripts/test-notifications.php
```

You should see a browser notification!

## Troubleshooting

### "Push notifications are not supported"

- Use HTTPS (or localhost for dev)
- Try Chrome/Firefox/Edge (Safari iOS doesn't support web push)
- Check if service worker registration fails

### "Firebase VAPID key not configured"

- Check `.env.local` has all Firebase variables set
- Restart dev server after changing .env.local
- Ensure VAPID key starts with `B` (not placeholder text)

### Service Worker Not Working

- Check DevTools → Application → Service Workers
- File must be in `public/` directory
- Clear cache and hard reload (Ctrl+Shift+R)

### No Notifications Received

- Check browser notification settings (allow for your domain)
- Check Notification.permission === 'granted'
- Verify token registered: Check network tab for `/api/v1/fcm/tokens/register`
- Check Drupal backend has Service Account JSON configured

## Browser Support

| Browser | Support | Notes |
|---------|---------|-------|
| Chrome 50+ | ✅ Full | Recommended |
| Firefox 44+ | ✅ Full | Recommended |
| Edge 79+ | ✅ Full | Chromium-based |
| Safari 16+ (macOS) | ⚠️ Limited | With user permission |
| Safari (iOS) | ❌ No | Apple doesn't support web push on iOS |
| Opera 37+ | ✅ Full | Chromium-based |

## Security

- All Firebase config values are safe to expose (they're public identifiers, not secrets)
- Service Account JSON (with private key) is only in Drupal backend
- FCM tokens are per-device and can be revoked
- HTTPS required in production (push notifications don't work on HTTP)

## Files Created

1. **lib/firebase/config.ts** - Firebase SDK initialization
2. **lib/hooks/usePushNotifications.ts** - React hook for push notifications
3. **components/notifications/PushNotificationPrompt.tsx** - Auto-prompt component
4. **components/notifications/NotificationCenter.tsx** - Notification bell icon
5. **public/firebase-messaging-sw.js** - Service worker for background messages

## Next Steps

1. **Configure Firebase** - Add your project credentials to `.env.local`
2. **Add to Layout** - Import and use `<PushNotificationPrompt />` in your root layout
3. **Add Notification Center** - Import and use `<NotificationCenter />` in your navbar
4. **Test** - Login and enable notifications
5. **Customize** - Style components to match your design system

For complete setup instructions, see [PUSH_NOTIFICATIONS_SETUP.md](./PUSH_NOTIFICATIONS_SETUP.md).
