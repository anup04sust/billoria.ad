# Billoria Notifications Module

User notification system for the Billoria billboard marketplace platform.

## Features

- ✅ Database-backed notification storage
- ✅ REST API endpoints for frontend integration
- ✅ **Firebase Cloud Messaging (FCM) push notifications**
- ✅ Cross-platform support (Web, Android, iOS)
- ✅ Device token management
- ✅ Multiple notification types (booking, verification, system, etc.)
- ✅ Priority levels (low, normal, high, urgent)
- ✅ Read/unread status tracking
- ✅ Auto-expiration for time-sensitive notifications
- ✅ Metadata support for contextual information (entity IDs, action URLs)
- ✅ Automatic cleanup of expired notifications and inactive tokens via cron

## Installation

```bash
cd /var/www/billoria.ad/cmsapi
ddev drush pm:enable billoria_notifications -y
ddev drush cr

# Configure Firebase Server Key
ddev drush config:set billoria_notifications.firebase server_key "YOUR_FIREBASE_SERVER_KEY" -y
```

**Get Firebase Server Key:**
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project → Project Settings → Cloud Messaging
3. Copy the "Server Key" (Legacy)

See [FCM_PUSH_NOTIFICATIONS.md](../../../application-wiki/FCM_PUSH_NOTIFICATIONS.md) for complete Firebase setup guide.

## API Endpoints

All endpoints require authentication.

### List Notifications

```bash
GET /api/v1/notifications
```

**Query Parameters:**
- `limit` (optional): Max notifications to return (default: 50, max: 100)
- `offset` (optional): Offset for pagination (default: 0)
- `unread_only` (optional): Filter to unread only (`true`/`false`)
- `type` (optional): Filter by notification type

**Response:**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "nid": "1",
        "uid": "5",
        "type": "booking",
        "title": "Booking Request Approved",
        "message": "Your booking request for Billboard #123 has been approved.",
        "metadata": {
          "billboard_id": 123,
          "booking_id": 456,
          "action_url": "/dashboard/bookings/456"
        },
        "is_read": false,
        "priority": "high",
        "created": "1712102400",
        "read_at": null,
        "expires_at": "1712188800"
      }
    ],
    "unreadCount": 3,
    "pagination": {
      "limit": 50,
      "offset": 0,
      "hasMore": false
    }
  },
  "timestamp": 1712102400
}
```

### Get Unread Count

```bash
GET /api/v1/notifications/unread-count
```

**Response:**
```json
{
  "success": true,
  "data": {
    "unreadCount": 3
  },
  "timestamp": 1712102400
}
```

### Mark Notification as Read

```bash
POST /api/v1/notifications/{nid}/mark-read
```

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": {
    "unreadCount": 2
  },
  "timestamp": 1712102400
}
```

### Mark All Notifications as Read

```bash
POST /api/v1/notifications/mark-all-read
```

**Response:**
```json
{
  "success": true,
  "message": "Marked 3 notifications as read",
  "data": {
    "updatedCount": 3,
    "unreadCount": 0
  },
  "timestamp": 1712102400
}
```

---

## Firebase Push Notifications

### Register Device Token

```bash
POST /api/v1/notifications/fcm/register
```

**Request Body:**
```json
{
  "token": "fcm-device-token",
  "deviceType": "web",
  "deviceName": "Chrome on Windows"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Device token registered successfully",
  "timestamp": 1712102400
}
```

### Unregister Device Token

```bash
POST /api/v1/notifications/fcm/unregister
```

**Request Body:**
```json
{
  "token": "fcm-device-token"
}
```

### List Registered Devices

```bash
GET /api/v1/notifications/fcm/tokens
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tokens": [
      {
        "id": "1",
        "deviceType": "web",
        "deviceName": "Chrome on Windows",
        "isActive": true,
        "created": "1712102400",
        "updated": "1712102400",
        "tokenPreview": "eXaMpLeToKeN123456..."
      }
    ],
    "count": 1
  },
  "timestamp": 1712102400
}
```

See [FCM_PUSH_NOTIFICATIONS.md](../../../application-wiki/FCM_PUSH_NOTIFICATIONS.md) for complete Firebase integration guide.

---

## API Endpoints

```bash
DELETE /api/v1/notifications/{nid}
```

**Response:**
```json
{
  "success": true,
  "message": "Notification deleted",
  "data": {
    "unreadCount": 2
  },
  "timestamp": 1712102400
}
```

## Programmatic Usage

### Create a Notification

```php
$notification_manager = \Drupal::service('billoria_notifications.manager');

$nid = $notification_manager->createNotification(
  uid: 5,  // User ID to notify
  type: 'booking',
  title: 'Booking Request Approved',
  message: 'Your booking request for Billboard #123 has been approved.',
  metadata: [
    'billboard_id' => 123,
    'booking_id' => 456,
    'action_url' => '/dashboard/bookings/456',
  ],
  priority: 'high',
  expires_at: strtotime('+7 days'),  // Optional expiration
  send_push: true  // Send FCM push notification (default: true)
);
```

### Notification Types

Recommended types:
- `booking` - Booking requests, approvals, rejections
- `verification` - KYC verification status updates
- `payment` - Payment confirmations, reminders
- `system` - Platform announcements, maintenance notices
- `message` - Direct messages or inquiries
- `alert` - Urgent notifications requiring immediate attention

### Priority Levels

- `low` - Informational updates
- `normal` - Standard notifications (default)
- `high` - Important updates requiring attention
- `urgent` - Critical alerts requiring immediate action

## Database Schema

The module creates two tables:

### billoria_notifications

- `nid` - Notification ID (primary key)
- `uid` - User ID (owner of notification)
- `type` - Notification type
- `title` - Notification title
- `message` - Notification message
- `metadata` - JSON metadata (entity IDs, URLs, etc.)
- `is_read` - Read status (0/1)
- `priority` - Priority level
- `created` - Creation timestamp

### billoria_fcm_tokens

- `id` - Token ID (primary key)
- `uid` - User ID (owner)
- `token` - FCM device token (unique)
- `device_type` - Device type (web, android, ios)
- `device_name` - Optional device name
- `is_active` - Active status (0/1)
- `created` - Creation timestamp
- `updated` - Last update timestamp
- `read_at` - Read timestamp
- `expires_at` - Expiration timestamp (optional)

## Integration Examples

### Billboard Booking Approved

```php
// In billboard booking approval logic
$notification_manager = \Drupal::service('billoria_notifications.manager');

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
```,
  send_push: true  // Auto-sends push notification

### KYC Verification Completed

```php
$notification_manager->createNotification(
  uid: $user->id(),
  type: 'verification',
  title: 'Verification Complete',
  message: 'Your KYC verification has been approved. You can now list billboards.',
  metadata: ['action_url' => '/dashboard/billboards/create'],
  priority: 'high'
);
```

### System Maintenance Notice

```php
// Notify all active users
$user_ids = \Drupal::entityQuery('user')
  ->condition('status', 1)
  ->accessCheck(FALSE)
  ->execute();

foreach ($user_ids as $uid) {
  $notification_manager->createNotification(
    uid: $uid,uring cron runs:
1. Deletes expired notifications
2. Cleans up inactive FCM tokens (older than 30 days)

Ensure cron is configured:

```bash
ddev drush cron
```

## Push Notifications

When creating a notification, push notifications are automatically sent to all registered devices for that user. To disable push for a specific notification:

```php
$notification_manager->createNotification(
  uid: $user->id(),
  type: 'system',
  title: 'In-App Only',
  message: 'This notification will not trigger a push.',
  send_push: false  // Disable push notification
);
```

## Frontend Integration

See guides:
- [NOTIFICATIONS_API.md](../../../application-wiki/NOTIFICATIONS_API.md) - In-app notifications API
- [FCM_PUSH_NOTIFICATIONS.md](../../../application-wiki/FCM_PUSH_NOTIFICATIONS.md) - Firebase push setup

## Cron Task

The module automatically deletes expired notifications during cron runs. Ensure cron is configured:

```bash
ddev drush cron
```

## Frontend Integration

See [application-wiki/NOTIFICATIONS_API.md](../../../application-wiki/NOTIFICATIONS_API.md) for Next.js integration examples.
