# Billoria Core Module

**Version**: 1.0.0  
**Drupal**: 10 | 11  
**Type**: Custom Business Logic Module

## Overview

The Billoria Core module provides the central business logic and services for the Billoria billboard advertising platform. It handles billboard management, booking workflows, agency operations, pricing calculations, and verification processes.

## Features

### Business Logic Services

1. **BillboardManager** - Billboard inventory management
   - Search and filter billboards
   - Check availability
   - Calculate pricing
   - Manage verification

2. **BookingManager** - Booking request workflows
   - Create booking requests
   - Validate dates and availability
   - Track bookings by user
   - Update booking status

3. **AgencyManager** - Agency operations
   - Get agency profiles
   - Search agencies by criteria
   - Generate agency statistics

4. **PricingCalculator** - Dynamic pricing engine
   - Calculate costs by pricing model (daily/weekly/monthly)
   - Apply duration-based discounts
   - Generate pricing breakdowns

5. **VerificationService** - Content verification workflows
   - Verify billboards, agencies, vendors
   - Track verification history
   - Permission-based access control

6. **NotificationService** - Communication hub
   - Email notifications
   - Status change alerts
   - Booking notifications

7. **WorkflowManager** - Automated workflows
   - Cron-based scheduled tasks
   - Cleanup expired bookings
   - Send reminders
   - Auto-update statuses

8. **ApiHelper** - API utilities
   - Format entities for JSON responses
   - Build standard API responses
   - Normalize data structures

### Custom Permissions

- `administer billoria` - Full platform administration
- `verify billboard` - Approve/reject billboards
- `verify agency_profile` - Approve/reject agencies
- `verify vendor_profile` - Approve/reject vendors
- `manage own billboards` - CRUD own billboards
- `view all billboards` - View all listings
- `create booking requests` - Submit bookings
- `manage own bookings` - Manage own bookings
- `manage agency bookings` - Manage agency bookings
- `approve booking requests` - Approve/reject bookings
- `view booking analytics` - Access reports
- `manage pricing` - Update pricing
- `send notifications` - Send manual notifications
- `access billoria api` - Access custom API

### Custom User Roles

Created automatically on module installation:

1. **Platform Administrator** (`platform_admin`)
   - Full platform access
   - Can verify all entities
   - Manage pricing and analytics

2. **Billboard Owner** (`billboard_owner`)
   - Manage own billboards
   - View booking requests
   - Respond to inquiries

3. **Agency** (`agency`)
   - View all billboards
   - Manage bookings for their billboards
   - Approve booking requests

4. **Brand User** (`brand_user`)
   - Search and view billboards
   - Create booking requests
   - Manage own bookings

5. **Vendor** (`vendor`)
   - View billboards
   - Submit bids for campaigns
   - Manage service requests

### Custom Hooks

- **hook_node_presave()** - Auto-generate billboard codes
- **hook_node_access()** - Custom access control
- **hook_entity_insert()** - Trigger notifications
- **hook_entity_update()** - Track status changes
- **hook_cron()** - Process scheduled tasks
- **hook_mail()** - Define email templates

### Custom API Endpoints

**1. Search Billboards**
```
GET /api/billoria/billboards/search

Query Parameters:
- district (optional)
- billboard_type (optional)
- availability_status (optional)
- verified (optional, default: true)
- min_price (optional)
- max_price (optional)
- limit (optional, default: 20)
- offset (optional, default: 0)

Response:
{
  "success": true,
  "message": "Success",
  "data": {
    "billboards": [...],
    "count": 10,
    "limit": 20,
    "offset": 0
  },
  "timestamp": 1234567890
}
```

**2. Check Billboard Availability**
```
GET /api/billoria/billboards/{billboard_id}/availability

Query Parameters:
- start_date (required, YYYY-MM-DD)
- end_date (required, YYYY-MM-DD)

Response:
{
  "success": true,
  "data": {
    "available": true,
    "billboard_id": 123,
    "start_date": "2026-04-01",
    "end_date": "2026-04-30",
    "pricing": {
      "base_price": 50000,
      "pricing_model": "monthly",
      "duration_days": 30,
      "unit_count": 1,
      "subtotal": 50000,
      "discount_rate": 0.05,
      "discount_amount": 2500,
      "total": 47500,
      "currency": "BDT"
    }
  }
}
```

## Dependencies

### Required Modules
- Node
- User
- Taxonomy
- Field
- Text
- Link
- Datetime
- Image
- File
- JSON:API
- REST
- Language
- Content Translation
- Pathauto
- Token

## Installation

1. Place module in `web/modules/custom/billoria_core/`
2. Enable the module:
   ```bash
   ddev drush pm:enable billoria_core -y
   ```
3. Clear cache:
   ```bash
   ddev drush cr
   ```

## Usage

### Using Services in Custom Code

```php
// Get the billboard manager service.
$billboard_manager = \Drupal::service('billoria_core.billboard_manager');

// Search for available billboards.
$billboards = $billboard_manager->getAvailableBillboards([
  'district' => 'Dhaka',
  'verified' => TRUE,
], 10, 0);

// Check availability.
$is_available = $billboard_manager->isAvailableForBooking(
  $billboard_node,
  '2026-04-01',
  '2026-04-30'
);

// Calculate pricing.
$pricing = $billboard_manager->calculateBillboardPrice(
  $billboard_node,
  '2026-04-01',
  '2026-04-30'
);
```

### Using Booking Manager

```php
$booking_manager = \Drupal::service('billoria_core.booking_manager');

// Create booking request.
$result = $booking_manager->createBookingRequest(123, [
  'start_date' => '2026-04-01',
  'end_date' => '2026-04-30',
  'budget' => 50000,
  'notes' => 'Campaign for new product launch',
]);

// Validate dates.
$validation = $booking_manager->validateBookingDates('2026-04-01', '2026-04-30');
if (!$validation['valid']) {
  // Handle errors.
  print_r($validation['errors']);
}
```

### Using Verification Service

```php
$verification_service = \Drupal::service('billoria_core.verification_service');

// Verify a billboard.
$verification_service->verify(
  $billboard_node,
  'verified',
  'All documents checked and approved'
);

// Get pending verifications count.
$pending = $verification_service->getPendingCount('billboard');
```

## Database Schema

### billoria_booking Table

Stores booking requests (used until custom entity is implemented):

| Field | Type | Description |
|-------|------|-------------|
| id | serial | Primary key |
| billboard_nid | int | Billboard node ID |
| user_uid | int | Requesting user ID |
| start_date | varchar(10) | Booking start date |
| end_date | varchar(10) | Booking end date |
| status | varchar(32) | Booking status |
| budget | float | Requested budget |
| final_price | float | Final agreed price |
| notes | text | Booking notes |
| created | int | Created timestamp |
| updated | int | Updated timestamp |

## Configuration

Module configuration stored in: `billoria_core.settings`

Access configuration at: `/admin/config/billoria`

## Pricing Logic

### Discount Tiers

| Duration | Discount |
|----------|----------|
| 1+ year | 20% |
| 6+ months | 15% |
| 3+ months | 10% |
| 1+ month | 5% |
| < 1 month | 0% |

### Pricing Models

- **Daily**: Base price × number of days
- **Weekly**: Base price × number of weeks (rounded up)
- **Monthly**: Base price × number of months (rounded up)
- **Campaign**: Flat base price

## Workflow States

### Billboard States
- Draft
- Pending Review
- Verified
- Rejected
- Archived

### Booking States
- Pending
- Approved
- Confirmed
- Active
- Completed
- Cancelled
- Rejected

## Event Subscribers

### BilloriaCoreSubscriber

Listens to:
- `KernelEvents::REQUEST` - Handle custom request processing

## Coding Standards

This module follows:
- Drupal 11 coding standards
- PHP 8.1+ typed properties and return types
- PSR-4 namespacing
- Dependency injection pattern
- Service container architecture

## Development

### Running Tests

```bash
# PHPUnit tests (when implemented)
ddev exec phpunit web/modules/custom/billoria_core/tests

# PHP CodeSniffer
ddev exec phpcs --standard=Drupal web/modules/custom/billoria_core
```

### Code Structure

```
billoria_core/
├── billoria_core.info.yml           # Module definition
├── billoria_core.services.yml       # Service definitions
├── billoria_core.permissions.yml    # Custom permissions
├── billoria_core.routing.yml        # Custom routes
├── billoria_core.module             # Hooks and utilities
├── billoria_core.install            # Install/uninstall hooks
├── README.md                        # This file
└── src/
    ├── Controller/
    │   └── BillboardApiController.php
    ├── Service/
    │   ├── BillboardManager.php
    │   ├── BookingManager.php
    │   ├── AgencyManager.php
    │   ├── PricingCalculator.php
    │   ├── VerificationService.php
    │   ├── NotificationService.php
    │   ├── WorkflowManager.php
    │   └── ApiHelper.php
    ├── EventSubscriber/
    │   └── BilloriaCoreSubscriber.php
    ├── Entity/                      # Custom entities (future)
    ├── Form/                        # Admin forms (future)
    └── Plugin/                      # Plugins (future)
```

## Future Enhancements

- [ ] Create Booking custom entity
- [ ] Implement Payment tracking entity
- [ ] Add Tender/Bid workflow
- [ ] Implement advanced analytics
- [ ] Add REST resources for custom entities
- [ ] Create admin dashboard
- [ ] Implement rate limiting
- [ ] Add caching layer
- [ ] Create batch operations
- [ ] Add export/import functionality

## Support

For issues or questions, contact the development team.

## License

GPL-2.0-or-later

---

**Maintained by**: Billoria Development Team  
**Last Updated**: March 26, 2026
