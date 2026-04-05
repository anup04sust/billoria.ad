# Admin Notification Interface

## Overview

The admin notification interface allows platform administrators to send notifications to users with integrated push notification support.

## Access

**URL**: `/admin/content/notifications/send`

**Permission Required**: `administer billoria notifications`

**Navigation**:
- From admin menu: Content → **Send Notification**
- Or visit: https://billoria-ad-api.ddev.site/admin/content/notifications/send

## Features

### 1. Recipient Selection

Choose who receives the notification:

- **Specific User**: Search and select a single user by username or email
- **All Users with Role**: Send to all users with a specific role:
  - `billboard_owner`
  - `agency`
  - `brand_user`
  - `vendor`
  - `platform_admin`
- **All Users**: Send to all active users (shows confirmation warning)

### 2. Notification Content

Configure the notification message:

- **Type**: System, Announcement, Welcome, Booking, Payment, Verification, Promotion
- **Title**: Short notification title (max 100 characters)
- **Message**: Main notification message (max 500 characters)
- **Priority**:
  - Low 📋 - Informational messages
  - Normal 🔔 - Standard notifications
  - High ⚠️ - Important updates
  - Urgent 🚨 - Critical alerts

### 3. Advanced Options

- **Action URL**: Optional deep link (e.g., `/dashboard/bookings`)
- **Expiration**: Set notification to expire after X days

### 4. Push Notification

- **Send as Push Notification**: ✅ Enabled by default
- Sends real-time push to all registered devices (web, Android, iOS)
- Users must have granted notification permissions

### 5. Live Preview

Real-time preview updates as you type, showing:
- Notification appearance
- Priority icon
- Title and message formatting

## Usage Examples

### Welcome Message (Single User)

```
Recipients: Specific User → Search "newuser@example.com"
Type: Welcome
Title: Welcome to Billoria! 🎉
Message: Your billboard marketplace journey starts here.
Priority: Normal
Push: ✅ Enabled
```

### System Announcement (All Users)

```
Recipients: All Users
Type: Announcement
Title: Platform Maintenance Scheduled
Message: Our platform will undergo maintenance on April 5th from 2-4 AM. Services may be temporarily unavailable.
Priority: High
Push: ✅ Enabled
Expires: 7 days
```

### Payment Alert (Role-Based)

```
Recipients: All Users with Role → billboard_owner
Type: Payment
Title: Payment Processing Update
Message: We've updated our payment processing system for faster settlements.
Priority: Normal
Push: ✅ Enabled
Action URL: /dashboard/payments
```

### Urgent Security Alert

```
Recipients: All Users
Type: System
Title: Security Alert 🚨
Message: Please update your password immediately. We detected suspicious activity.
Priority: Urgent
Push: ✅ Enabled
```

## API Integration

Notifications sent via admin interface:

1. **Create notification record** in database
2. **Send push notification** (if enabled) to all user's registered FCM tokens
3. **Log activity** in system logs
4. **Show in-app** notification bell icon

Users see notifications:
- Push notification on their device
- In-app notification center (`/api/v1/notifications`)
- Notification bell badge count

## Testing Push Notifications

1. **Login as test user** in frontend: https://billoria-ad.ddev.site:3001
2. **Allow notifications** when prompted (wait 60 seconds)
3. **Send test notification** via admin interface
4. **Verify receipt**:
   - Check push notification appears
   - Check notification bell icon
   - Verify in notification center

## Command Line Alternative

Use the test script for quick testing:

```bash
cd cmsapi
ddev exec php scripts/test-push-notification.php 1 "Title" "Message"
```

## Troubleshooting

### Push Notifications Not Sending

1. Check Firebase V1 API configured: `/admin/config/billoria/notifications`
2. Verify user has registered FCM token:
   ```bash
   ddev drush sql-query "SELECT * FROM user_fcm_tokens WHERE uid = 1"
   ```
3. Check system logs: Reports → Recent log messages

### User Not Receiving

- User must be logged in and have allowed browser notifications
- Check if token is registered: `/api/v1/notifications/fcm/tokens`
- Verify notification permissions in browser settings

### Preview Not Updating

- Clear browser cache
- Check JavaScript console for errors
- Ensure jQuery and Drupal AJAX libraries loaded

## Security

- Only users with `administer billoria notifications` permission can access
- Rate limiting applied to prevent spam
- All actions logged in system logs
- CSRF tokens required for form submission

## Related Documentation

- [AUTHENTICATION.md](../application-wiki/AUTHENTICATION.md) - User authentication
- [PROFILE_API.md](../application-wiki/PROFILE_API.md) - User management
- [test-push-notification.php](../cmsapi/scripts/test-push-notification.php) - CLI testing
