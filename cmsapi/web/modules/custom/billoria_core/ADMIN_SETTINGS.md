# Billoria Core Admin Settings

## Overview

The Billoria Platform Settings admin interface provides centralized configuration for all core platform features, business rules, and system settings.

## Access

- **URL**: `/admin/config/billoria/core`
- **Direct Link**: `http://billoria-ad-api.ddev.site/admin/config/billoria/core`
- **Navigation**: Admin Menu (Top Level) → **Billoria** → Platform Settings
- **Permission Required**: `administer site configuration`
- **Icon**: Custom billboard icon displayed in Gin admin theme

## Configuration Sections

### 1. Platform Information
- **Platform Name**: Your marketplace name (default: "Billoria")
- **Tagline**: Short description of your platform
- **Contact Email**: Primary contact email for the platform
- **Support Phone**: Support phone number (e.g., +880 1812345678)

### 2. Business Settings
- **Platform Commission Rate (%)**: Percentage commission on bookings (default: 10%)
- **VAT Rate (%)**: Value Added Tax rate for Bangladesh (default: 15%)
- **Currency**: Platform currency (BDT, USD, EUR)
- **Minimum Booking Duration (days)**: Minimum billboard rental period (default: 7 days)

### 3. Verification & Trust
- **Auto-approve agency registrations**: Skip manual approval for agencies
- **Require document verification**: Mandate verification document uploads
- **Require email verification**: Users must verify email to access platform
- **Require phone verification**: Users must verify phone number
- **Minimum Trust Score for Bookings**: Threshold users need to make bookings (default: 40)

### 4. API Configuration
- **Enable API access**: Master switch for all API endpoints
- **API Rate Limit (requests/minute)**: Max requests per user per minute (default: 60)
- **CORS Allowed Origins**: One origin per line (e.g., http://localhost:3000)

### 5. Email Configuration
- **From Email Address**: Email used for system emails (default: noreply@billoria.ad)
- **From Name**: Name shown in system emails
- **Admin Notification Email**: Where to send admin alerts

### 6. Feature Flags
- **Enable Booking System**: Allow users to book billboards
- **Enable Payment Processing**: Enable online payment gateway integration
- **Enable Reviews & Ratings**: Allow users to review billboard owners
- **Enable Analytics Dashboard**: Show analytics to users
- **Maintenance Mode**: Put platform in maintenance mode

### 7. Geographic Settings
- **Default Country**: Bangladesh, India, Pakistan, or Nepal
- **Default Timezone**: Asia/Dhaka (GMT+6), Asia/Kolkata, Asia/Karachi, or UTC

### 8. Advanced Settings
- **Debug Mode**: Enable verbose logging (**Disable in production!**)
- **API Cache Lifetime (seconds)**: How long to cache API responses (default: 3600)
- **Session Timeout (seconds)**: User session expiration time (default: 86400)

## Usage Examples

### Reading Settings in Code

```php
// Get config object
$config = \Drupal::config('billoria_core.settings');

// Get specific values
$commission_rate = $config->get('commission_rate');
$api_enabled = $config->get('api_enabled');
$trust_threshold = $config->get('trust_score_threshold');

// Check feature flags
if ($config->get('enable_booking')) {
  // Booking system is enabled
}

if ($config->get('maintenance_mode')) {
  // Platform is in maintenance mode
}
```

### Using in Services

```php
namespace Drupal\my_module\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

class MyService {

  protected $config;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('billoria_core.settings');
  }

  public function calculateCommission($amount) {
    $rate = $this->config->get('commission_rate') / 100;
    return $amount * $rate;
  }

  public function canUserBook($user_trust_score) {
    $threshold = $this->config->get('trust_score_threshold');
    return $user_trust_score >= $threshold;
  }
}
```

### Checking in Controllers

```php
public function myMethod() {
  $config = \Drupal::config('billoria_core.settings');
  
  // Check if feature is enabled
  if (!$config->get('enable_reviews')) {
    return new JsonResponse([
      'error' => 'Reviews feature is disabled',
    ], 403);
  }
  
  // Check maintenance mode
  if ($config->get('maintenance_mode')) {
    return new JsonResponse([
      'error' => 'Platform is under maintenance',
    ], 503);
  }
}
```

## Default Values

When the module is installed, the following defaults are set:

```yaml
site_name: 'Billoria'
commission_rate: 10
vat_rate: 15
currency: 'BDT'
min_booking_duration: 7
trust_score_threshold: 40
api_enabled: true
api_rate_limit: 60
enable_booking: true
enable_payments: false  # Disabled until payment gateway is integrated
enable_reviews: true
enable_analytics: true
maintenance_mode: false
debug_mode: false
```

## Related Documentation

- [SMS Settings](/admin/config/billoria/sms) - Configure Alpha SMS integration for OTP verification
- [User Verification](../../../application-wiki/VERIFICATION_API.md) - Verification API documentation
- [Trust Score System](../../../application-wiki/Trust-Score.md) - How trust scores work

## Troubleshooting

### Settings not appearing
1. Clear cache: `ddev drush cr`
2. Check you have `administer site configuration` permission
3. Verify the module is enabled

### Changes not taking effect
- Some settings may require cache clear after modification
- API-related changes might need application restart

### Can't access settings page
- Ensure you're logged in as admin
- Generate admin login link: `ddev drush user:login`

## Development Notes

- Configuration schema defined in `config/schema/billoria_core.schema.yml`
- Default values in `config/install/billoria_core.settings.yml`
- Form class: `src/Form/BilloriaSettingsForm.php`
- Route: `billoria_core.routing.yml`
- Menu link: `billoria_core.links.menu.yml`

### Custom Admin Icon

The Billoria admin menu features a custom billboard icon optimized for the Gin admin theme:

- **Icon File**: `assets/images/billoria-icon.svg`
- **Styling**: `assets/css/admin-menu-icon.css`
- **Library**: Defined in `billoria_core.libraries.yml` as `admin_icon`
- **Auto-loaded**: Icon appears automatically on all admin pages via `hook_page_attachments()`

The icon is a simple, recognizable SVG showing a billboard with the letter "B" inside, designed to work at small sizes in the admin toolbar.
