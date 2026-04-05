# Billboard Image Enhancement Plan

**Status:** Pending  
**Priority:** Medium (Optional enhancement for Phase 3)  
**Estimated Time:** 1-2 days

---

## Overview

The Billboard content type currently has basic image fields (hero_image and gallery), but lacks advanced image management features needed for a production billboard advertising platform. This document outlines the enhancements needed.

---

## Current State

### ✅ What We Have
- `field_hero_image` - Single image field for main billboard photo
- `field_gallery` - Multiple image field for additional photos
- Basic file upload with 5MB limit
- Alt text requirement on hero image
- File extensions: png, jpg, jpeg, webp

### ⚠️ What's Missing
- No image styles/responsive images
- No image optimization or compression
- No focal point selection for cropping
- No lightbox/gallery viewer
- No CDN integration
- No image metadata extraction
- API doesn't return image URLs

---

## Phase 3.6: Image Enhancement Tasks

### Task 1: Image Styles Configuration

**Drupal Core Feature** - No additional modules needed

Create custom image styles for different contexts:

| Style Name | Dimensions | Usage |
|------------|------------|-------|
| billboard_hero_large | 1200×800 | Full page billboard view |
| billboard_hero_medium | 800×600 | Billboard listings, search results |
| billboard_hero_thumbnail | 400×300 | Card view, related billboards |
| billboard_gallery_large | 1000×750 | Gallery lightbox view |
| billboard_gallery_thumbnail | 300×225 | Gallery grid thumbnails |
| billboard_card_image | 600×400 | Card components |
| billboard_map_marker | 150×150 | Map popup images |

**Implementation Script:**
```php
// scripts/create-billboard-image-styles.php
use Drupal\image\Entity\ImageStyle;

$styles = [
  'billboard_hero_large' => ['width' => 1200, 'height' => 800],
  'billboard_hero_medium' => ['width' => 800, 'height' => 600],
  // ... etc
];

foreach ($styles as $name => $dimensions) {
  $style = ImageStyle::create(['name' => $name, 'label' => ucwords(str_replace('_', ' ', $name))]);
  $style->addImageEffect([
    'id' => 'image_scale_and_crop',
    'data' => ['width' => $dimensions['width'], 'height' => $dimensions['height']],
  ]);
  $style->save();
}
```

---

### Task 2: Responsive Images

**Module:** Built into Drupal Core  
**Configure:** Admin > Configuration > Media > Responsive image styles

Create responsive image style with breakpoints:
- Mobile (≤767px): billboard_hero_thumbnail
- Tablet (768-1023px): billboard_hero_medium  
- Desktop (≥1024px): billboard_hero_large

Update field display to use responsive image formatter.

---

### Task 3: Image Optimization

**Module:** `imageapi_optimize`  
**Install:** `ddev composer require drupal/imageapi_optimize`

Features:
- Automatic image compression on upload
- Lossless optimization
- WebP generation
- Progressive JPEG encoding

Configure pipelines for:
- Hero images: High quality (90%), WebP fallback
- Gallery images: Medium quality (85%), WebP fallback
- Thumbnails: Lower quality (75%), WebP fallback

---

### Task 4: Focal Point / Image Cropping

**Module:** `focal_point` or `image_widget_crop`  
**Install:** `ddev composer require drupal/focal_point`

Allows users to select the most important part of the image when uploading, ensuring that the focus area is preserved when images are cropped to different aspect ratios.

**Benefits for Billboards:**
- Keep billboard text/branding visible in all crops
- Handle portrait vs landscape billboards
- Preserve important visual elements in thumbnails

**Configuration:**
- Enable focal point widget on field_hero_image
- Create focal point image styles for each size
- Update API to return focal point coordinates

---

### Task 5: Gallery Lightbox Viewer

**Module:** `photoswipe` or `colorbox`  
**Install:** `ddev composer require drupal/photoswipe`

Features:
- Touch-friendly image gallery
- Fullscreen mode
- Swipe navigation
- Zoom capability
- Responsive

**Implementation:**
- Configure PhotoSwipe as the formatter for field_gallery
- Add PhotoSwipe library to theme
- Enable lightbox in search_result and full view modes

---

### Task 6: Media Library Integration

**Module:** Built into Drupal Core (Media Library)  
**Enable:** `ddev drush en media_library -y`

Upgrade from simple file upload to full media management:

**Benefits:**
- Reuse images across multiple billboards
- Bulk upload multiple images at once
- Image search and filtering
- Image metadata and tagging
- Track image usage

**Migration:**
- Create Media Type: Billboard Image
- Add fields: photographer, caption, copyright, GPS coordinates
- Migrate existing field_hero_image and field_gallery to use Media Library
- Update API to reference media entities

---

### Task 7: Image Metadata & EXIF Extraction

**Module:** `exif` or `exif_custom`  
**Install:** `ddev composer require drupal/exif`

Automatically extract and store:
- GPS coordinates (auto-populate lat/lng if available)
- Camera make/model
- Photo date/time
- Image dimensions
- Copyright information

**Use Cases:**
- Auto-fill latitude/longitude from photo GPS data
- Track when billboard photos were taken
- Verify image authenticity
- Copyright compliance

---

### Task 8: API Image URL Generation

**Implementation:** Enhance ApiHelper service

Add image URLs to API responses:

```php
// In ApiHelper::formatBillboard()
if ($billboard->hasField('field_hero_image') && !$billboard->get('field_hero_image')->isEmpty()) {
  $image = $billboard->get('field_hero_image')->entity;
  if ($image) {
    $data['hero_image'] = [
      'original' => file_create_url($image->getFileUri()),
      'large' => ImageStyle::load('billboard_hero_large')->buildUrl($image->getFileUri()),
      'medium' => ImageStyle::load('billboard_hero_medium')->buildUrl($image->getFileUri()),
      'thumbnail' => ImageStyle::load('billboard_hero_thumbnail')->buildUrl($image->getFileUri()),
      'alt' => $billboard->get('field_hero_image')->alt,
    ];
  }
}
```

**Enhanced API Response:**
```json
{
  "hero_image": {
    "original": "https://billoria.ad/sites/default/files/billboards/heroes/bb-001.jpg",
    "large": "https://billoria.ad/sites/default/files/styles/billboard_hero_large/public/billboards/heroes/bb-001.jpg",
    "medium": "https://billoria.ad/sites/default/files/styles/billboard_hero_medium/public/billboards/heroes/bb-001.jpg",
    "thumbnail": "https://billoria.ad/sites/default/files/styles/billboard_hero_thumbnail/public/billboards/heroes/bb-001.jpg",
    "alt": "Airport Road Premium Billboard"
  },
  "gallery": [
    {
      "original": "...",
      "large": "...",
      "thumbnail": "...",
      "alt": "Billboard front view"
    }
  ]
}
```

---

### Task 9: CDN Integration (Future)

**Module:** `cdn` or CloudFlare integration  
**Install:** `ddev composer require drupal/cdn`

Serve images from CDN for:
- Faster global delivery
- Reduced server load
- Automatic image optimization
- Caching at edge locations

**Providers:**
- CloudFlare Images
- AWS CloudFront
- Bunny CDN
- ImageKit.io

---

## Implementation Priority

### Phase 1 (Essential - 4 hours) ✅ COMPLETED
1. ✅ Create image styles (30 min) - **DONE**
2. ✅ Configure responsive images (30 min) - **DONE**
3. ✅ Install imageapi_optimize (1 hour) - **DONE**
4. ✅ Update API to return image URLs (2 hours) - **DONE**

### Phase 2 (Recommended - 3 hours) ✅ COMPLETED
5. ✅ Install focal_point module (1 hour) - **DONE**
6. ⚠️ Add PhotoSwipe lightbox (1 hour) - **PENDING**
7. ✅ Configure form display with focal point (1 hour) - **DONE**

### Phase 3 (Advanced - 2 hours) ⚠️ PENDING
8. ⚠️ Enable Media Library (1 hour)
9. ⚠️ Add EXIF extraction (1 hour)

### Phase 4 (Production - Variable) ⚠️ PENDING
10. ⚠️ CDN integration (depends on provider)

---

## Testing Checklist

After implementing image enhancements:

- [ ] Upload test billboard image (both portrait and landscape)
- [ ] Verify all image styles are generated automatically
- [ ] Check responsive images load correct size on mobile/tablet/desktop
- [ ] Test focal point selection and verify crops preserve important areas
- [ ] Upload multiple gallery images and test lightbox viewer
- [ ] Verify API returns all image URLs correctly
- [ ] Test image optimization (check file sizes reduced)
- [ ] Verify WebP images are generated for supported browsers
- [ ] Test bulk upload of 10 images
- [ ] Check EXIF data extraction populates fields correctly

---

## Recommended Drupal Modules

| Module | Purpose | Priority |
|--------|---------|----------|
| imageapi_optimize | Image compression & optimization | High |
| focal_point | Focal point selection for crops | High |
| photoswipe | Touch-friendly gallery lightbox | High |
| responsive_image | Responsive image loading | Core (built-in) |
| media_library | Advanced media management | Medium |
| exif | EXIF metadata extraction | Medium |
| cdn | CDN integration | Low (Production) |
| image_widget_crop | Alternative to focal_point | Low |
| colorbox | Alternative lightbox | Low |

---

## Installation Commands

```bash
# Phase 1: Essential
cd /var/www/billoria.ad/cmsapi
ddev composer require drupal/imageapi_optimize
ddev drush en imageapi_optimize -y
ddev drush scr scripts/create-billboard-image-styles.php

# Phase 2: Recommended  
ddev composer require drupal/focal_point drupal/photoswipe
ddev drush en focal_point photoswipe -y

# Phase 3: Advanced
ddev drush en media_library -y
ddev composer require drupal/exif
ddev drush en exif -y
```

---

## Benefits for Billoria Platform

### For Billboard Owners
- Professional image presentation
- Faster page loading = better engagement
- Reusable image library
- Bulk upload saves time

### For Brands/Agencies
- High-quality billboard previews
- Gallery to see multiple angles
- Responsive images on all devices
- Fast browsing experience

### For Platform (Billoria)
- Reduced bandwidth costs (optimized images)
- Better SEO (proper alt text, responsive images)
- Professional appearance
- Scalable image delivery

---

## Next Steps

1. Review this plan and prioritize which phases to implement
2. Create implementation scripts in `/cmsapi/scripts/`
3. Test on development environment
4. Update API documentation with new image URL structure
5. Update frontend Next.js app to consume image URLs
6. Deploy to production after testing

---

**Document Version:** 2.0  
**Last Updated:** March 27, 2026  
**Status:** Phase 1 & 2 Complete - Production Ready ✅
