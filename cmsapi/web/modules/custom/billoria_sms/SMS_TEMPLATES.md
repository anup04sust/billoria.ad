# SMS Template Guide

## OTP Template Configuration

The OTP message template is configurable via admin UI at `/admin/config/billoria/sms`.

### Available Placeholders

- `{{code}}` - The verification code (e.g., 123456)
- `{{minutes}}` - Validity period in minutes (default: 10)
- `{{brand}}` - Brand name (Billoria)

### Default Template

```
Your Billoria verification code is {{code}}. Valid for {{minutes}} minutes. Do not share this code.
```

**Output example:**
```
Your Billoria verification code is 123456. Valid for 10 minutes. Do not share this code.
```

## Template Examples

### Short (93 chars)
```
{{code}} is your Billoria verification code. Valid {{minutes}}min.
```

### Formal (105 chars)
```
Dear user, your {{brand}} verification code is {{code}}. Please use within {{minutes}} minutes.
```

### Security-focused (77 chars)
```
{{brand}}: Your OTP is {{code}}. DO NOT share. Expires in {{minutes}} min.
```

### Bengali (118 chars)
```
আপনার Billoria যাচাইকরণ কোড: {{code}}। {{minutes}} মিনিটের জন্য বৈধ।
```

### Minimal (45 chars)
```
{{code}} - {{brand}} OTP ({{minutes}}min)
```

### With Brand First (89 chars)
```
BILLORIA: Your verification code is {{code}}. Valid for {{minutes}} minutes.
```

## SMS Best Practices

### Length Considerations
- **Single SMS**: 160 characters (recommended)
- **Concatenated SMS**: 153 chars per segment (charges multiply)
- Aim for under 160 to keep costs down

### Security Guidelines
1. **Never include links** in OTP messages (phishing risk)
2. **Include expiry time** to create urgency
3. **Add security warning** ("Do not share")
4. **Brand identification** at start or before code

### Formatting Tips
- Use **ALL CAPS** for brand name (optional, for emphasis)
- Keep code clearly separated from text
- Use simple, direct language
- Avoid special characters that might break encoding

### Language Support
- **English**: Standard for most users
- **Bengali**: Use Unicode characters (ensure proper encoding)
- **Mixed**: "Your Billoria code" works for bilingual users

### Character Encoding
- **GSM-7**: Standard ASCII characters (160 chars)
- **Unicode**: Bengali/special chars (70 chars per SMS)
- Test templates before deployment

## Testing Templates

### Via Drush
```bash
ddev exec drush php-eval "
\$sms = \Drupal::service('billoria_sms.sender');
\$result = \$sms->sendOtp('01812345678', '123456', 10);
print_r(\$result);
"
```

### Via Admin UI
1. Go to `/admin/config/billoria/sms`
2. Enter test phone number
3. Click "Send Test SMS"
4. Check received message format

### Character Count Check
```php
$template = 'Your Billoria verification code is {{code}}. Valid for {{minutes}} minutes. Do not share this code.';
$message = str_replace(['{{code}}', '{{minutes}}'], ['123456', '10'], $template);
echo strlen($message); // Should be < 160
```

## Common Issues

### Message Too Long
**Problem:** SMS split into multiple parts, higher costs

**Solution:** Shorten template
```
Before: Your Billoria verification code is {{code}}. Valid for {{minutes}} minutes. Do not share this code with anyone. (116 chars)

After: {{brand}} OTP: {{code}}. Valid {{minutes}}min. Don't share. (51 chars)
```

### Special Characters Not Showing
**Problem:** Unicode characters breaking in SMS

**Solution:** 
- Test with actual device
- Stick to GSM-7 charset if possible
- Or embrace Unicode with shorter messages

### Code Not Clear
**Problem:** Users missing the code in message

**Solution:** Put code early or use formatting
```
{{code}} - Your Billoria verification code ({{minutes}}min validity)
```

## Localization

### Multi-language Support

You can create language-specific templates in custom code:

```php
use Drupal\Core\Language\LanguageInterface;

$language = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT);

$templates = [
  'en' => 'Your Billoria code is {{code}}. Valid {{minutes}}min.',
  'bn' => 'আপনার কোড {{code}}। {{minutes}} মিনিট বৈধ।',
];

$template = $templates[$language->getId()] ?? $templates['en'];
```

## Sender ID Configuration

A **Sender ID** (e.g., "Billoria") replaces the phone number in SMS.

### Requirements
- Must be approved by Alpha SMS
- 3-11 characters (alphanumeric)
- No special characters
- Case-sensitive

### Application Process
1. Contact Alpha SMS support
2. Provide business documentation
3. Wait for approval (1-3 days)
4. Configure in admin UI

### Without Sender ID
Messages will show from a numeric sender (e.g., +8801XXX...)

## Analytics Tracking

Track OTP delivery success:

```php
$result = $sms_sender->sendOtp($phone, $code);

if ($result['success']) {
  $request_id = $result['request_id'];
  
  // Later, check delivery
  $report = $sms_sender->getReport($request_id);
  // Status: 'Sent', 'Delivered', 'Failed', etc.
}
```

## Cost Optimization

1. **Keep messages under 160 chars** (single SMS)
2. **Use number normalization** (avoid international rates)
3. **Set reasonable OTP expiry** (10 min default)
4. **Monitor failed deliveries** (check reports)
5. **Consider bulk rates** for high volume

## Support

**Template Questions:**
- Contact Billoria dev team

**Sender ID / API Issues:**
- Alpha SMS support: https://sms.net.bd/
