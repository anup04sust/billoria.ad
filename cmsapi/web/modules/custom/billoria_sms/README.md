# Billoria SMS Module

Alpha SMS API integration for Drupal 11, providing SMS messaging capabilities for the Billoria platform.

## Features

- **Send SMS**: Send single or bulk SMS messages
- **OTP Support**: Dedicated method for sending verification codes
- **Delivery Reports**: Track message delivery status
- **Balance Checking**: Monitor account balance
- **Phone Normalization**: Automatic formatting to Bangladesh standards (880XXXXXXXXXX)
- **Error Handling**: Comprehensive error code mapping
- **Admin UI**: Configuration form with test SMS functionality

## Installation

1. Enable the module:
   ```bash
   ddev drush en billoria_sms
   ```

2. Configure the API settings at `/admin/config/billoria/sms`:
   - Enter your Alpha SMS API key
   - (Optional) Set default sender ID
   - Test the connection with a test SMS

## API Documentation

### Send SMS

```php
$sms_sender = \Drupal::service('billoria_sms.sender');

// Single recipient
$result = $sms_sender->sendSms('01812345678', 'Hello from Billoria!');

// Multiple recipients
$result = $sms_sender->sendSms(
  ['01812345678', '01987654321'],
  'Bulk message'
);

// With options
$result = $sms_sender->sendSms(
  '01812345678',
  'Scheduled message',
  [
    'schedule' => '2026-04-15 14:30:00',
    'sender_id' => 'Billoria',
  ]
);

if ($result['success']) {
  $request_id = $result['request_id'];
}
```

### Send OTP

```php
$sms_sender = \Drupal::service('billoria_sms.sender');
$result = $sms_sender->sendOtp('01812345678', '123456', 10);
```

### Get Delivery Report

```php
$report = $sms_sender->getReport($request_id);
if ($report['success']) {
  $status = $report['data']['request_status'];
  $charge = $report['data']['request_charge'];
}
```

### Check Balance

```php
$balance = $sms_sender->getBalance();
if ($balance['success']) {
  $amount = $balance['balance'];
}
```

## Phone Number Format

Phone numbers are automatically normalized to Bangladesh format:
- `01812345678` → `8801812345678`
- `8801812345678` → `8801812345678`
- `1812345678` → `8801812345678`

## Error Codes

- **0**: Success
- **400**: Missing or invalid parameter
- **403**: No permissions
- **404**: Resource not found
- **405**: Authorization required
- **409**: Server error
- **410**: Account expired
- **411**: Reseller account expired
- **412**: Invalid schedule
- **413**: Invalid sender ID
- **414**: Message is empty
- **415**: Message is too long
- **416**: No valid number found
- **417**: Insufficient balance
- **420**: Content blocked
- **421**: Test mode restriction

## Integration Example

### OTP Verification Controller

```php
// In your controller
$sms_sender = \Drupal::service('billoria_sms.sender');

// Generate OTP
$otp = random_int(100000, 999999);

// Send via SMS
$result = $sms_sender->sendOtp($phone_number, (string) $otp, 10);

if ($result['success']) {
  // Store OTP with expiry in session/database
  // Return success to frontend
}
```

## Configuration

Settings are stored in `billoria_sms.settings` config:
- `api_key`: Your Alpha SMS API key
- `sender_id`: Default sender ID (optional)
- `otp_template`: Customizable OTP message template with placeholders

### OTP Template

Configure via admin UI or drush:
```bash
ddev exec drush config:set billoria_sms.settings otp_template "{{code}} is your Billoria code. Valid {{minutes}}min." -y
```

**Placeholders:**
- `{{code}}` - Verification code
- `{{minutes}}` - Validity period
- `{{brand}}` - Brand name (Billoria)

See [SMS_TEMPLATES.md](SMS_TEMPLATES.md) for examples and best practices.

## Requirements

- Drupal 11
- GuzzleHttp client
- Alpha SMS account with API access

## API Provider

**Alpha SMS**
- Website: https://sms.net.bd/
- API Documentation: See included `Alpha SMS.pdf`
- Get API Key: https://sms.net.bd/api

## Support

For issues related to:
- **Module functionality**: Contact Billoria development team
- **API access/billing**: Contact Alpha SMS support

## License

Proprietary - Part of Billoria Platform
Copyright © 2026 DreamSteps
