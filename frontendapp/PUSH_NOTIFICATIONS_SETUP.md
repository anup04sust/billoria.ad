# Push Notifications Setup Guide

## Overview

This guide shows how to configure Firebase Cloud Messaging (FCM) push notifications for the Billoria frontend.

## Prerequisites

1. **Firebase Project** with Cloud Messaging enabled
2. **Service Account JSON** configured in Drupal backend
3. **HTTPS** (push notifications require secure context)

## Step 1: Get Firebase Web App Credentials

### 1.1 Go to Firebase Console

1. Open [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Click **⚙️ gear icon** → **Project Settings**

### 1.2 Add Web App (if not already added)

1. Scroll to **"Your apps"** section
2. Click the **</>** Web icon
3. Enter app nickname: `billoria-frontend`
4. **Don't** check "Firebase Hosting" checkbox
5. Click **"Register app"**
6. Copy the Firebase configuration (you'll need these values)

The config looks like this:
```javascript
const firebaseConfig = {
  apiKey: "AIzaSy...",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project.appspot.com",
  messagingSenderId: "246037782925",
  appId: "1:246037782925:web:..."
};
```

### 1.3 Get VAPID Key (Web Push Certificate)

1. Still in **Project Settings**
2. Go to **Cloud Messaging** tab
3. Scroll to **"Web Push certificates"** section
4. If you don't see a key pair, click **"Generate key pair"**
5. Copy the **Key pair** value (starts with `B...`)

## Step 2: Configure Frontend Environment Variables

Edit `/var/www/billoria.ad/frontendapp/.env.local`:

```env
# Existing variables
NEXT_PUBLIC_URL=http://billoria-ad.ddev.site:3000
NEXT_PUBLIC_API_BASE_URL=https://billoria-ad-api.ddev.site

# Firebase Configuration (from Step 1.2)
NEXT_PUBLIC_FIREBASE_API_KEY=AIzaSy...
NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
NEXT_PUBLIC_FIREBASE_PROJECT_ID=your-project-id
NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID=246037782925
NEXT_PUBLIC_FIREBASE_APP_ID=1:246037782925:web:...

# VAPID Key (from Step 1.3)
NEXT_PUBLIC_FIREBASE_VAPID_KEY=BNdF...
```

**Important:** Replace all `your_*_here` placeholder values with your actual Firebase config values.

## Step 3: Add Components to Your Layout

### Option A: Auto-prompt for logged-in users (Recommended)

Edit your root layout file (e.g., `app/layout.tsx`):

```tsx
import { PushNotificationPrompt } from '@/components/notifications/PushNotificationPrompt';
import { NotificationCenter } from '@/components/notifications/NotificationCenter';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html>
      <body>
        {/* Your existing layout */}
        <nav>
          {/* Add notification center icon to navbar */}
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

### Option B: Manual button to request permission

```tsx
import { usePushNotifications } from '@/lib/hooks/usePushNotifications';
import { getSession } from '@/lib/api/auth';

export function NotificationButton() {
  const session = getSession();
  const { permission, requestPermission, isLoading } = usePushNotifications(session?.user?.uid || null);
  
  if (permission === 'granted') {
    return <span>✓ Notifications enabled</span>;
  }
  
  return (
    <button onClick={requestPermission} disabled={isLoading}>
      {isLoading ? 'Enabling...' : 'Enable Notifications'}
    </button>
  );
}
```

## Step 4: Test Notifications

### 4.1 Restart Frontend Dev Server

```bash
cd /var/www/billoria.ad/frontendapp
# Stop current dev server (Ctrl+C)
pnpm dev
```

### 4.2 Login to Frontend

1. Open https://billoria-ad.ddev.site:3000 (or http://localhost:3000)
2. Login with your account
3. You should see a notification prompt in the bottom-right corner
4. Click **"Enable Notifications"**
5. Browser will show permission dialog → Click **"Allow"**

### 4.3 Send Test Notification from Drupal

```bash
cd /var/www/billoria.ad/cmsapi
ddev drush scr scripts/test-notifications.php
```

You should receive a push notification in your browser!

## Step 5: Verify Everything Works

### Check Service Worker Registration

1. Open browser DevTools (F12)
2. Go to **Application** tab → **Service Workers**
3. You should see `firebase-messaging-sw.js` registered

### Check FCM Token Registration

1. DevTools → **Console** tab
2. Look for: `FCM token registered: { success: true, ... }`

### Check Notification Permission

```javascript
// In browser console
Notification.permission // Should return "granted"
```

## Troubleshooting

### "Push notifications are not supported"

**Cause:** Browser doesn't support notifications or you're on HTTP (not HTTPS)

**Solutions:**
- Use HTTPS (required for production)
- For local dev: Use `localhost` or set up HTTPS with mkcert
- Try Chrome/Firefox/Edge (Safari on iOS doesn't support web push)

### "Firebase VAPID key not configured"

**Cause:** Environment variable is missing or still has placeholder value

**Solution:**
1. Check `.env.local` file
2. Make sure `NEXT_PUBLIC_FIREBASE_VAPID_KEY` is set
3. Value should start with `B` (not `your_vapid_key_here`)
4. Restart dev server after changing .env.local

### "Failed to register FCM token with backend"

**Cause:** Backend API is not accessible or user is not logged in

**Solutions:**
1. Check if you're logged in: `getSession()` in console
2. Verify backend is running: `ddev list` in cmsapi folder
3. Check browser console for CORS errors
4. Verify Drupal billoria_notifications module is enabled

### Service Worker Not Registering

**Cause:** Service worker file not found or JavaScript error

**Solutions:**
1. Check file exists: `/var/www/billoria.ad/frontendapp/public/firebase-messaging-sw.js`
2. File must be in `public/` directory (served at root URL)
3. Clear browser cache and hard reload (Ctrl+Shift+R)
4. Check console for errors

### Notifications Not Appearing

**Possible causes:**
1. Browser permission denied - Check `Notification.permission`
2. Browser notification settings - Enable for your site
3. Do Not Disturb mode enabled (OS level)
4. Service worker not running - Check DevTools → Application → Service Workers

## Production Deployment

### Requirements

1. **Valid SSL certificate** (Let's Encrypt recommended)
2. **Firebase project** in production mode
3. **Service Account JSON** uploaded to production Drupal
4. **Environment variables** set in production `.env.local`

### Deployment Checklist

- [ ] Firebase config values copied to production
- [ ] VAPID key configured
- [ ] HTTPS enabled on production domain
- [ ] Service worker file deployed to public root
- [ ] Drupal backend has Service Account JSON configured
- [ ] Test notification sent successfully

## Security Notes

### Client-side Configuration (Safe to Expose)

These values are safe to expose in frontend code:
- Firebase API key, Auth domain, Project ID
- VAPID key
- Messaging Sender ID, App ID

**Why?** These are public identifiers, not authentication credentials. Firebase security rules and backend validation protect your data.

### Server-side Configuration (Keep Secret)

These should NEVER be in frontend code:
- ❌ Service Account JSON with private key
- ❌ Firebase Admin SDK credentials
- ❌ OAuth credentials

**These belong only in Drupal backend configuration.**

## API Reference

See [NOTIFICATIONS_API.md](../application-wiki/NOTIFICATIONS_API.md) for complete REST API documentation.

## Support

- **Frontend Issues:** Check browser console for errors
- **Backend Issues:** Check Drupal logs: `ddev drush watchdog:show`
- **Firebase Issues:** Firebase Console → Analytics → Debug View
