# Firebase V1 API Setup Guide 🔥

## Quick Setup (5 Minutes)

### Step 1: Get Service Account JSON

1. **Open Firebase Console**: https://console.firebase.google.com/
2. **Select your project** (or create one if you don't have it)
3. Click the **⚙️ gear icon** → **Project Settings**
4. Go to **Service Accounts** tab
5. Click **"Generate new private key"** button
6. Click **"Generate key"** (confirm the dialog)
7. A JSON file will download to your computer

### Step 2: Configure Drupal

1. **Go to Drupal admin**: `/admin/config/billoria/notifications`
2. **Check** ✅ "Use Firebase V1 API (Recommended)"
3. **Open the downloaded JSON file** with a text editor
4. **Copy the entire JSON content** (from `{` to `}`)
5. **Paste it** into the "Service Account JSON" textarea
6. **Save configuration**

### Step 3: Test It

Run the test script:
```bash
cd /var/www/billoria.ad/cmsapi
ddev drush scr scripts/test-notifications.php
```

You should see:
```
✓ Module is enabled
✓ Database tables exist
✓ Created notification: Welcome
✓ Registered FCM token
✓ Unregistered FCM token

All tests passed!
```

## What Changed?

### Before (Legacy API - No Longer Works)
- Used Simple Server Key: `AAAA1234567890...`
- Endpoint: `https://fcm.googleapis.com/fcm/send`
- **Status**: ❌ Deprecated and disabled by Google

### After (V1 API - Current Standard)
- Uses Service Account JSON credentials
- Endpoint: `https://fcm.googleapis.com/v1/projects/PROJECT_ID/messages:send`
- **Status**: ✅ Active and supported

## Security Notes

### Is Service Account JSON Safe?

**Yes**, when stored in Drupal's configuration:
- Stored in database (not in code repository)
- Protected by Drupal's access controls
- Only admins with "administer billoria notifications" permission can see it
- Never exposed to frontend/JavaScript

**Best Practices**:
1. ✅ Store in Drupal config (as we do)
2. ✅ Add JSON file to `.gitignore` if you save a copy locally
3. ❌ Never commit JSON to Git
4. ❌ Never expose in frontend code

### Service Account Permissions

The service account JSON has these permissions in your Firebase project:
- Send push notifications
- Manage FCM tokens
- Read project configuration

**It does NOT have**:
- Billing access
- Project ownership rights
- Ability to delete your project

You can revoke the service account anytime in Firebase Console → Service Accounts.

## Troubleshooting

### "Firebase V1 API not initialized"

**Cause**: Service Account JSON is invalid or missing

**Solution**:
1. Check that you pasted the complete JSON (starts with `{`, ends with `}`)
2. Verify it has `project_id` and `private_key` fields
3. Re-download fresh JSON from Firebase Console if needed
4. Clear Drupal cache: `ddev drush cr`

### "403 Permission Denied"

**Cause**: Service account lacks FCM permissions

**Solution**:
1. Firebase Console → IAM & Admin
2. Find `firebase-adminsdk-xxxxx@your-project.iam.gserviceaccount.com`
3. Add role: **Firebase Cloud Messaging Admin**
4. Wait 1-2 minutes for propagation

### "Invalid JSON format"

**Cause**: JSON is corrupted or incomplete

**Solution**:
1. Use a JSON validator: https://jsonlint.com/
2. Check for:
   - Missing quotes
   - Extra commas
   - Truncated content
3. Re-copy from the original file

## Mobile App Integration

### Android (Flutter/React Native)

```dart
// Get FCM token
String? token = await FirebaseMessaging.instance.getToken();

// Register with Drupal
await http.post(
  Uri.parse('http://billoria-ad-api.ddev.site/api/v1/fcm/tokens/register'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'token': token,
    'device_type': 'android',
    'device_name': 'Pixel 6',
  }),
);
```

### iOS (Swift)

```swift
// Get FCM token
Messaging.messaging().token { token, error in
    guard let token = token else { return }
    
    // Register with Drupal
    let url = URL(string: "http://billoria-ad-api.ddev.site/api/v1/fcm/tokens/register")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("application/json", forHTTPHeaderField: "Content-Type")
    
    let body = ["token": token, "device_type": "ios", "device_name": "iPhone 14"]
    request.httpBody = try? JSONSerialization.data(withJSONObject: body)
    
    URLSession.shared.dataTask(with: request).resume()
}
```

## API Reference

See [NOTIFICATIONS_API.md](../application-wiki/NOTIFICATIONS_API.md) for complete API documentation.

## Performance & Limits

### Firebase Quotas (Free Tier)
- **Send rate**: Unlimited (within reason)
- **Concurrent connections**: Unlimited
- **Message size**: 4096 bytes per notification
- **Token storage**: Handled by Drupal (not Firebase)

### Drupal Performance
- Token cleanup runs daily (configurable)
- Inactive tokens deleted after 30 days (configurable)
- No rate limiting on token registration (add if needed)

## Migration from Legacy API

If you were using the Legacy API before:

1. **Remove old server key**: Leave "Firebase Server Key" blank
2. **Uncheck** "Use Firebase V1 API" temporarily
3. **Add Service Account JSON** as described above
4. **Re-check** "Use Firebase V1 API"
5. **Save and test**

No changes needed in your mobile app code—FCM tokens work the same way.

## Support

- **Firebase Issues**: https://firebase.google.com/support
- **Drupal Module Issues**: Contact your development team
- **API Documentation**: See `/application-wiki/` folder
