# Email Logo Setup

## Current Implementation

The email template now uses **base64 encoded images** that are embedded directly into the email HTML. This ensures:
- ✅ Logo displays immediately without external requests
- ✅ Works even if recipient blocks external images  
- ✅ No broken image icons
- ✅ Better email client compatibility

## Logo Priority

The system checks for logo files in this order:

1. `public://logo.png` (Drupal public files)
2. `public://billoria-logo.png`
3. `themes/custom/billoria/logo.png`
4. `DRUPAL_ROOT/logo.png`
5. **Fallback**: SVG logo (built-in)

## Add Your Custom Logo

### Option 1: Upload to Drupal (Recommended)

```bash
# Copy your logo to Drupal public files
cp /path/to/your/logo.png cmsapi/web/sites/default/files/logo.png

# Or use DDEV
ddev exec cp /path/to/logo.png /var/www/html/web/sites/default/files/logo.png
```

### Option 2: Theme Directory

```bash
# Create theme directory if needed
mkdir -p cmsapi/web/themes/custom/billoria

# Copy logo
cp /path/to/your/logo.png cmsapi/web/themes/custom/billoria/logo.png
```

### Option 3: Custom SVG Logo

Edit `ApiVerificationController.php` line ~810 to customize the SVG:

```php
protected function getBilloriaSvgLogo() {
  $svg = <<<SVG
<svg width="150" height="40" viewBox="0 0 150 40" xmlns="http://www.w3.org/2000/svg">
  <!-- Your custom SVG code here -->
</svg>
SVG;
  return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
```

## Logo Requirements

**Recommended Specs**:
- **Format**: PNG (with transparency) or SVG
- **Dimensions**: 150-200px wide, 40-60px tall
- **File Size**: < 50KB (important for email size)
- **Background**: Transparent or white

**Supported Formats**:
- PNG (best for photos/complex graphics)
- JPG (photos only, no transparency)
- SVG (best for icons/logos)
- GIF (basic graphics)

## Testing

After adding your logo:

```bash
# Clear cache
ddev drush cr

# Send test email
# Click "Verify Email" on profile page

# Check in Mailpit
ddev mailpit
```

## Technical Details

**How It Works**:

1. `getLogoBase64()` checks for logo files
2. Reads file and converts to base64
3. Creates data URI: `data:image/png;base64,iVBORw0KG...`
4. Embeds in email template as `<img src="{{ logo_base64 }}">`

**Data URI Format**:
```
data:[mime-type];base64,[base64-encoded-data]
```

Example:
```html
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA..." alt="Logo">
```

## Current Fallback Logo

A professional SVG logo is used if no file is found:
- Blue gradient background (#1e40af)
- White "BILLORIA" text
- Billboard icon
- Gold accent dot

This ensures emails always have branding even without a custom logo file.

## Email Client Compatibility

**Base64 images work in**:
- ✅ Gmail (all platforms)
- ✅ Outlook 2007-2021
- ✅ Apple Mail
- ✅ Yahoo Mail
- ✅ ProtonMail
- ✅ Thunderbird
- ✅ Mobile clients (iOS Mail, Android Gmail)

**Size Limit**: Most email clients support base64 images up to 100KB. Keep logo under 50KB for best compatibility.
