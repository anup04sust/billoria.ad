# Billoria Billboard Platform - Project Roadmap

**Last Updated:** March 27, 2026  
**Current Phase:** Billboard Inventory Complete → Moving to Search & Browse

---

## ✅ Phase 1: Foundation & Infrastructure (COMPLETED)

### 1.1 Development Environment
- ✅ DDEV local environment configured (v1.25.1)
- ✅ Drupal 11.3.5 installed with PHP 8.4
- ✅ MariaDB 11.8 database
- ✅ Git version control with GitHub remote
- ✅ Next.js frontend app scaffolded

### 1.2 Core Modules & Configuration
- ✅ JSON:API enabled for headless architecture
- ✅ REST API configured
- ✅ Admin Toolbar for backend management
- ✅ Gin admin theme (v5.0) for modern UI
- ✅ Pathauto & Token for URL management
- ✅ Language modules (English/Bengali support)
- ✅ AI integration with Ollama (phi3:mini)

### 1.3 Custom Module: billoria_core
- ✅ 8 service classes with business logic:
  - BillboardManager
  - PricingCalculator
  - VerificationService
  - AvailabilityChecker
  - BookingCoordinator
  - SearchService
  - RecommendationEngine
  - NotificationService
- ✅ 15 custom permissions defined
- ✅ 5 user roles created (but need refinement - see Phase 2)
- ✅ billoria_booking database table
- ✅ Custom API endpoints
- ✅ Event subscribers and hooks

### 1.4 Taxonomy Infrastructure (COMPLETED)
- ✅ 13 vocabularies created with 743 total terms:
  - **upazila_thana**: 495 terms (all Bangladesh upazilas with duplicate handling)
  - area_zone: 78 commercial zones
  - district: 64 districts
  - road_type: 20 types with codes
  - placement_type: 17 types
  - media_format: 15 formats
  - city_corporation: 12 corporations
  - traffic_direction: 9 directions
  - division: 8 divisions
  - availability_status: 7 statuses
  - booking_mode: 7 modes
  - illumination_type: 6 types
  - visibility_class: 5 classes
  - road_name: 0 terms (vocabulary ready)

### 1.5 Location Hierarchy (COMPLETED)
- ✅ 5-tier structure: Division → District → Upazila/Thana → City Corporation → Area/Zone
- ✅ All relational mappings tested and verified
- ✅ Duplicate upazila names handled with "(District)" suffix
- ✅ Form display configurations for custom fields
- ✅ 28 upazilas with unique naming (e.g., "Kaliganj (Gazipur)", "Kaliganj (Jhenaidah)")

---

## ✅ Phase 2: User Management & Authentication (COMPLETED - March 27, 2026)

### 2.1 Custom Module: billoria_accounts ✅
- ✅ Module created with proper structure and dependencies
- ✅ 3 API Controllers (Registration, Verification, Profile)
- ✅ 2 Form classes (Brand and Agency registration)
- ✅ Mail hooks for email verification system
- ✅ User login redirect to organization dashboard
- ✅ Comprehensive API documentation (API_DOCUMENTATION.md)

### 2.2 Organization Content Type ✅
- ✅ Created "Organization" content type with **35 fields**:
  - **Common (15 fields):** title, field_org_type, field_official_email, field_official_phone, field_website, field_division, field_district, field_city_corporation, field_full_address, field_postal_code, field_business_reg_number, field_tin, field_establishment_year, field_org_logo, field_mobile_banking
  - **Brand-specific (4 fields):** field_parent_company, field_annual_budget_range, field_booking_duration, field_preferred_regions
  - **Agency-specific (6 fields):** field_agency_services, field_portfolio_size, field_owns_inventory, field_operations_contact, field_finance_contact, field_industry_category (Note: geographic_focus covered by preferred_regions)
  - **Owner-specific (4 fields):** field_inventory_count, field_maintenance_capability, field_installation_services, field_coverage_districts, field_total_coverage_sqft
  - **System (6 fields):** field_trust_score, field_verification_status, field_verification_docs, field_profile_completion, field_primary_admin, field_team_members
- ✅ Field display configured for different organization types
- ✅ Node entity with proper content type structure

### 2.3 User Entity Extensions ✅
- ✅ Added **13 custom fields** to user entity:
  - field_mobile_number (telephone, required)
  - field_designation (text)
  - field_department (text)
  - field_email_verified (boolean, default FALSE)
  - field_phone_verified (boolean, default FALSE)
  - field_verification_token (text)
  - field_token_expiry (timestamp)
  - field_phone_otp (text)
  - field_phone_otp_expiry (timestamp)
  - field_organization (entity ref to Organization, multiple)
  - field_active_organization (entity ref, single)
  - field_is_primary_admin (boolean)
  - field_secondary_email (email)

### 2.4 REST API Endpoints ✅
- ✅ **POST /api/v1/register** - Single endpoint for all account types (brand/agency/owner)
  - Creates user + organization in atomic transaction
  - Sends verification email automatically
  - Returns userId, organizationId, verification status
- ✅ **POST /api/v1/verify-email** - Email verification with token validation
- ✅ **POST /api/v1/request-phone-otp** - Generate 6-digit OTP for phone verification
- ✅ **POST /api/v1/verify-phone** - Validate OTP and mark phone as verified
- ✅ **POST /api/v1/resend-verification** - Resend verification email
- ✅ **GET /api/v1/user/profile** - Get user profile with organization data
- ✅ **GET /api/v1/organization/{nid}/status** - Get trust score and profile completion

### 2.5 Verification System ✅
- ✅ Email verification service:
  - Secure token generation with 1-hour expiry
  - Email sending via Drupal mail system
  - Token validation endpoint
  - Updates field_email_verified on success
  - Increases trust score (+10 points)
- ✅ Phone/SMS verification service:
  - 6-digit OTP generation
  - 10-minute OTP expiry
  - OTP validation endpoint
  - Updates field_phone_verified on success
  - Increases trust score (+10 points)
- ✅ Trust score calculation service:
  - Base score: 50 points
  - Email verified: +10
  - Phone verified: +10
  - Profile completion: +30 (based on required fields)
  - Scale: 0-100

### 2.6 Profile Completion System ✅
- ✅ Profile completion calculator service
- ✅ Tracks required fields per account type
- ✅ Returns completion percentage (0-100%)
- ✅ Stored in field_profile_completion

### 2.7 Documentation ✅
- ✅ **20+ comprehensive wiki pages** in `application-wiki/`:
  - API_OVERVIEW.md - API architecture and authentication
  - REGISTRATION_API.md - Registration endpoint details
  - VERIFICATION_API.md - Email and phone verification
  - PROFILE_API.md - User profile endpoints
  - AUTHENTICATION.md - Login/logout/session management
  - Organization-Schema.md - All 35 organization fields
  - Field-Reference.md - All 48 custom fields (13 user + 35 org)
  - Account-Types.md - Brand, Agency, Owner differences
  - Trust-Score.md - Trust score calculation logic
  - RBAC.md - Role and permission structure
  - Error-Handling.md - Error codes and responses
  - Next.js-Integration.md - Frontend integration guide
  - Testing-Guide.md - API testing examples
  - API-Client-Examples.md - 80+ code examples (7 languages)
  - Taxonomy-Reference.md - Taxonomy vocabularies
  - QUICK_START.md - Getting started guide
  - Changelog.md - Version history
  - _Sidebar.md, _Footer.md - Wiki navigation

### 2.8 Multi-Organization Support ✅
- ✅ Users can belong to multiple organizations (field_organization is multi-value)
- ✅ Active organization context switching (field_active_organization)
- ✅ Primary admin flag per user (field_is_primary_admin)
- ✅ Team member management ready (field_team_members on organization)

### 2.9 Admin & Business Logic ✅
- ✅ Organization verification workflow (Draft → Pending → Verified)
- ✅ Trust score system (0-100 scale)
- ✅ Profile completion tracking (0-100%)
- ✅ User login redirect to organization dashboard
- ✅ Mail templates for verification and notifications

**Phase 2 Status:** 100% Complete ✅  
**Total Fields Created:** 48 (13 user + 35 organization)  
**Total API Endpoints:** 7  
**Documentation Pages:** 20+

---

## ✅ Phase 3: Billboard Content Type & Inventory (COMPLETED - March 27, 2026)

### 3.1 Billboard Content Type ✅
- ✅ Created "Billboard" content type with **36 custom fields**:
  - **Identification (2):** field_billboard_id, title
  - **Media & Display (6):** field_media_format, field_placement_type, field_display_size, field_width_ft, field_height_ft, field_illumination_type
  - **Location (10):** field_division, field_district, field_upazila_thana, field_city_corporation, field_area_zone, field_road_name, field_road_type, field_latitude, field_longitude, field_facing_direction
  - **Traffic & Visibility (5):** field_traffic_direction, field_visibility_class, field_visibility_distance, field_lane_count, field_has_divider
  - **Commercial & Pricing (4):** field_rate_card_price, field_currency, field_commercial_score, field_traffic_score
  - **Booking (2):** field_booking_mode, field_availability_status
  - **Ownership (3):** field_owner_organization (entity ref to Organization), field_owner_vendor_name (legacy), field_owner_contact_number
  - **Status & Flags (2):** field_is_premium, field_is_active
  - **Media & Notes (3):** field_hero_image, field_gallery (multiple), field_notes
- ✅ Configured field widgets with proper form organization (9 logical sections)
- ✅ Set up 5 view modes: default (full), teaser, card, map_marker, search_result
- ✅ Created form display with logical field grouping and placeholders

### 3.2 Billboard API Endpoints ✅
- ✅ **POST /api/v1/billboard/create** - Create new billboard (owner only)
- ✅ **GET /api/v1/billboard/list** - List billboards with filters (limit, offset, sorting)
  - Supports filtering by: division, district, area_zone, media_format, availability_status, is_premium, price range, owner_organization
- ✅ **GET /api/v1/billboard/{id}** - Get single billboard details
- ✅ **PATCH/PUT /api/v1/billboard/{id}** - Update billboard (owner only)
- ✅ **DELETE /api/v1/billboard/{id}** - Delete billboard (owner + admin)
- ✅ Enhanced ApiHelper service to format billboard data with entity references

### 3.3 Billboard Business Logic ✅
- ✅ Extended BillboardManager service with:
  - createBillboard() method - validates required fields, creates billboard
  - updateBillboard() method - updates allowed fields, enforces permissions
  - getAvailableBillboards() - query with filters, sorting, pagination
- ✅ Field mapping for all 36 billboard fields
- ✅ Entity reference handling (taxonomy terms, organization nodes)
- ✅ Validation for required fields during creation

### 3.4 Permissions & Access Control ✅
- ✅ Billboard Owner role: create, edit own, delete own billboard content
- ✅ Brand User role: view published billboards
- ✅ Agency User role: view published billboards
- ✅ Administrator role: full control (edit any, delete any)
- ✅ Field-level access control for owner_organization

### 3.5 Sample Data ✅
- ✅ Created 5 sample billboards for testing:
  - Airport Road Premium Billboard (BB-DH-001)
  - Gulshan Avenue Billboard (BB-DH-002)
  - Mirpur Road Digital Billboard (BB-DH-003)
  - Banani Overpass Billboard (BB-DH-004)
  - Dhanmondi 27 Road Billboard (BB-DH-005)
- ✅ All billboards linked to proper taxonomies and organization

### 3.6 Image Management (MOSTLY COMPLETE - Core features implemented)
- [x] **Image Styles Configuration** ✅
  - [x] Create billboard_hero_large (1200x800)
  - [x] Create billboard_hero_medium (800x600)
  - [x] Create billboard_hero_thumbnail (400x300)
  - [x] Create billboard_gallery_large (1000x750)
  - [x] Create billboard_gallery_thumbnail (300x225)
  - [x] Create billboard_card_image (600x400)
  - [x] Create billboard_map_marker (150x150)
- [x] **Hero Image (Featured Image) Enhancement** ✅
  - [x] Configure responsive image styles
  - [x] Add focal point module for cropping control
  - [x] Set up automatic image optimization (imageapi_optimize installed)
  - [x] Configure form display with focal point widget
  - [ ] Add watermark overlay for preview mode (optional)
- [x] **Gallery Images Enhancement** ✅
  - [x] Configure multi-image upload widget
  - [x] Add focal point to gallery images
  - [ ] Implement image lightbox/modal viewer (PhotoSwipe pending)
  - [ ] Add bulk image upload capability (core feature)
  - [x] Configure image optimization (compress on upload)
  - [ ] Add image metadata extraction (EXIF data)
- [x] **Image Validation & Processing** ✅
  - [x] Enforce minimum resolution (800x600 for hero, 600x400 for gallery)
  - [x] Enforce maximum file size (5MB per image)
  - [x] Image styles with consistent aspect ratios
  - [x] Automatic image style generation on upload
- [x] **API Image Handling** ✅
  - [x] Return image URLs with different sizes in API responses
  - [x] Image URL generation for all configured styles
  - [x] Image metadata (alt, title, dimensions, mime_type, size)
  - [ ] Support signed URLs for private images (future)
  - [ ] Add CDN integration for image delivery (production)
- [ ] **Admin Image Management** (Phase 3 - Optional)
  - [ ] Create image library/media browser
  - [ ] Add image search and filtering  
  - [ ] Implement image bulk operations (delete, replace)
  - [ ] Add image usage tracking (which billboards use which images)

**Phase 3 Status:** Core Complete (95%) - Essential image features implemented ✅  
**Total Billboard Fields:** 36  
**Total API Endpoints:** 5 (CRUD + List)  
**Sample Data:** 5 billboards  
**Image Enhancement:** 22/25 implemented (Essential + Recommended complete)

---

---

## 📋 Phase 4: Search, Browse & Discovery (PENDING)

### 4.1 Views Configuration
- [ ] Create billboard listing views:
  - All billboards (public)
  - My inventory (for owners)
  - Featured billboards
  - Recently added
- [ ] Implement faceted search (by location, format, price range, etc.)
- [ ] Create map view integration (with geolocation)
- [ ] Build owner portfolio view

### 4.2 Search Service Enhancement
- [ ] Enhance SearchService in billoria_core
- [ ] Implement radius-based search
- [ ] Add availability date range filtering
- [ ] Create search preset saving for brands/agencies
- [ ] Build recommendation engine using commercial_score + traffic_score

### 4.3 JSON:API Endpoints for Frontend
- [ ] Configure JSON:API resources for billboards
- [ ] Set up relationship includes (location hierarchy, owner, etc.)
- [ ] Add custom filters and sorting
- [ ] Implement pagination
- [ ] Test with Next.js frontend

---

## 📋 Phase 5: Booking & Inquiry System (PENDING)

### 5.1 Booking Inquiry Content Type
- [ ] Create "Booking Inquiry" content type (per content model):
  - Reference: billboard (entity ref)
  - Requester: organization (entity ref), contact person
  - Dates: start_date, end_date, campaign_duration
  - Budget: proposed_budget, currency
  - Campaign: campaign_name, creative_brief
  - Status: inquiry_status (pending/quoted/accepted/rejected/expired)
  - Communication: messages, attachments
- [ ] Build inquiry submission form
- [ ] Configure permissions (who can create/view inquiries)

### 5.2 Booking Management
- [ ] Enhance BookingCoordinator service
- [ ] Implement availability conflict checking
- [ ] Add booking confirmation workflow
- [ ] Create invoice generation (basic)
- [ ] Build booking calendar view
- [ ] Add booking status tracking

### 5.3 Communication System
- [ ] Notification service for new inquiries (email + in-app)
- [ ] Owner quote submission form
- [ ] Brand/Agency acceptance flow
- [ ] Automated reminder for pending inquiries
- [ ] Message thread per booking

---

## 📋 Phase 6: Advanced Features (FUTURE)

### 6.1 Billboard Cluster
- [ ] Create "Billboard Cluster" content type
- [ ] Group billboards by location/campaign
- [ ] Bulk pricing calculator
- [ ] Package booking system

### 6.2 Campaign Management
- [ ] Campaign content type
- [ ] Multi-billboard campaign planning
- [ ] Campaign analytics dashboard
- [ ] ROI tracking

### 6.3 Reporting & Analytics
- [ ] Owner revenue reports
- [ ] Brand spending analytics
- [ ] Popular location insights
- [ ] Booking conversion metrics

### 6.4 Payment Integration
- [ ] Payment gateway integration (bKash, Nagad, SSL Commerz)
- [ ] Invoice & receipt generation
- [ ] Payment tracking
- [ ] Refund management

---

## 🎯 IMMEDIATE NEXT STEPS (Priority Order)

### ✅ Sprint 1: User Onboarding (COMPLETED - March 27, 2026)

**Completed:**
- ✅ Organization content type with 35 custom fields
- ✅ User entity extended with 13 custom fields
- ✅ Custom registration API endpoint (POST /api/v1/register)
- ✅ Email verification service with token system
- ✅ Phone verification service with OTP
- ✅ Profile completion calculator & trust score system
- ✅ Multi-organization support & switching
- ✅ Comprehensive API documentation (20+ pages)
- ✅ billoria_accounts module created and tested

---

### 🚀 Sprint 2: Billboard Content Type (COMPLETED - March 27, 2026)

**Completed:**
- ✅ Billboard content type with 36 custom fields
- ✅ Field storage and configurations  
- ✅ Form display with logical organization
- ✅ 5 view modes (full, teaser, card, map_marker, search_result)
- ✅ Permissions and access control
- ✅ 5 REST API endpoints (CRUD + List)
- ✅ BillboardManager service with create/update methods
- ✅ ApiHelper enhanced for billboard formatting
- ✅ 5 sample billboards created

**Pending Enhancements:**
- ⚠️ Image styles and responsive images
- ⚠️ Hero image and gallery optimization
- ⚠️ Image validation and processing
- ⚠️ Image API URL generation

---

### 🎨 Sprint 2.5: Billboard Image Enhancement (OPTIONAL - ESTIMATED: 1-2 days)

**Priority 1: Image Styles**
- [ ] Create 7 image styles for different use cases
- [ ] Configure responsive image module
- [ ] Set up automatic WebP generation
- [ ] Add image style derivatives to API responses

**Priority 2: Hero Image Enhancement**
- [ ] Install and configure image_widget_crop or focal_point module
- [ ] Add image optimization on upload (ImageAPI Optimize)
- [ ] Configure lazy loading attributes
- [ ] Add alt text requirements

**Priority 3: Gallery Enhancement**
- [ ] Configure media library for better image management
- [ ] Add drag-and-drop reordering for gallery
- [ ] Implement lightbox viewer (Colorbox or Photoswipe)
- [ ] Add bulk upload capability

**Priority 4: Validation & Processing**
- [ ] Add custom validation for image dimensions
- [ ] Enforce max file size (5MB)
- [ ] Auto-generate thumbnails on upload
- [ ] Add EXIF data extraction for geocoding

---

**Priority 1: Create Billboard Content Type**
- [ ] Create "Billboard" content type with fields per Drupal_Content_Model_Sheet.md:
  - Identification: billboard_id, title, slug
  - Media: media_format, placement_type, display_size, dimensions
  - Location: division, district, upazila_thana, city_corporation, area_zone, road_name, road_type
  - Geographic: latitude, longitude, facing_direction, visibility_distance
  - Traffic: traffic_direction, lane_count, has_divider, visibility_class
  - Features: illumination_type, is_premium, hero_image, gallery
  - Commercial: rate_card_price, currency, commercial_score, traffic_score
  - Booking: booking_mode, availability_status
  - Owner: owner_organization (entity ref), owner_contact_name, owner_contact_number
  - Status: is_active, notes
- [ ] Configure field widgets and formatters for each field type
- [ ] Set up view modes (full, teaser, card, map_marker, search_result)
- [ ] Create form displays for different user roles (owner_edit, admin_edit)

**Priority 2: Billboard Validation & Rules**
- [ ] Implement geolocation validation (lat/lng within Bangladesh bounds: 20.5-26.5 N, 88-92.7 E)
- [ ] Add duplicate detection service (same location + same owner)
- [ ] Configure file upload limits for images (hero: 5MB, gallery: 10 images × 5MB)
- [ ] Add required field validation based on booking_mode:
  - "direct_booking" requires: rate_card_price, availability_status
  - "inquiry_only" requires: owner_contact_name, owner_contact_number
- [ ] Implement auto-generate billboard_id service (format: BB-{district_code}-{serial})

**Priority 3: Billboard Ownership & Permissions**
- [ ] Link billboard to owner organization (entity reference, required)
- [ ] Only users with "billboard_owner" role can create billboards
- [ ] Owners can only edit their own billboards (permission: "edit own billboard content")
- [ ] Admins can edit any billboard (permission: "edit any billboard content")
- [ ] Implement access control check for owner_organization field

**Priority 4: Billboard Listing Views**
- [ ] Create "My Inventory" view for owners (filter by current user's organization)
- [ ] Create "All Billboards" public listing (exclude inactive)
- [ ] Add "Featured Billboards" view (is_premium = TRUE)
- [ ] Create "Recently Added" view (sort by created date DESC, limit 10)
- [ ] Configure exposed filters (location, price range, media_format, availability)

**Priority 5: Basic API Endpoints**
- [ ] Create BillboardApiController in billoria_core
- [ ] **POST /api/v1/billboard/create** - Create new billboard (owner only)
- [ ] **GET /api/v1/billboard/list** - List billboards with filters
- [ ] **GET /api/v1/billboard/{nid}** - Get single billboard details
- [ ] **PATCH /api/v1/billboard/{nid}** - Update billboard (owner only)
- [ ] **DELETE /api/v1/billboard/{nid}** - Delete billboard (owner + admin)

**Expected Outcomes:**
- Billboard content type fully functional
- Owners can create and manage their inventory
- Basic validation and access control in place
- API endpoints ready for frontend integration
- Sample data created for testing (10-15 billboards)

---

### Sprint 3: Search & Browse (ESTIMATED: 2-3 days)
- [ ] Create Billboard con90% ✅ (enhanced with Billboard CRUD)  
**billoria_accounts Module:** 100% ✅  
**User Onboarding:** 100% ✅  
**Organization Management:** 100% ✅  
**Billboard Content:** 100% ✅ **← JUST COMPLETED**  
**Billboard API:** 100% ✅ **← JUST COMPLETED**  
**Search & Browselboard listing views
- [ ] Test with sample data

### Sprint 3: Search & Browse (Estimated: 2-3 days)
- [ ] Configure billboard search view with filters
- [ ] Implement map integration
- [ ] Build faceted search
- [ ] Create JSON:API endpoints
- [ ] Test with Next.js frontend

### Sprint 4: Booking & Inquiry (Estimated: 3-4 days)
- [ ] Create Booking Inquiry content type
- [ ] Build inquiry submission flow
- [ ] Implement notification system
- [ ] Create owner quote interface
- [ ] Build booking acceptance workflow

---

## 📊 Current Status Summary

**Infrastructure:** 100% ✅  
**Taxonomy System:** 100% ✅  
**billoria_core Module:** 80% ✅ (needs role refinement for Phase 3)  
**billoria_accounts Module:** 100% ✅ **← JUST COMPLETED**  
**User Onboarding:** 100% ✅ **← JUST COMPLETED**  
**Organization Management:** 100% ✅ **← JUST COMPLETED**  
**Billboard Content:** 0% 🔴 **← NEXT FOCUS**  
**Booking System:** 0% 🔴  
**Frontend Integration:** 0% 🔴

---

## 🔧 Technical Debt & Improvements

### High Priority
- [ ] **Billboard Image Management** - Configure image styles, responsive images, and optimization
- [ ] **Hero Image Enhancement** - Add focal point selection and WebP conversion
- [ ] **Gallery Image Processing** - Implement bulk upload and lightbox viewer
- [ ] Refine user roles in billoria_core (align with onboarding design)
- [ ] Add proper error handling to all billoria_core services
- [ ] Implement logging for booking operations
- [ ] Add data validation in PricingCalculator

### Medium Priority
- [ ] road_name vocabulary needs seed data (major highways)
- [ ] Add caching to SearchService for performance
- [ ] Optimize taxonomy term queries
- [ ] Add automated testing for core services

### Low Priority
- [ ] Expand upazila coverage if needed (currently 495 - complete)
- [ ] Add more area_zone terms as business expands
- [ ] Create taxonomy import UI for admins
- [ ] Build data export tools

---

## 📝 Notes & Decisions

### Key Architectural Decisions:
1. **Separate Organization entity from User entity** - allows multi-user teams per org ✅ Implemented
2. **Progressive profile completion** - don't block access, encourage completion ✅ Implemented
3. **3-tier verification system** - Email → Phone → Business Documents ✅ Email + Phone implemented
4. **Three account types:** Brand, Agency, Owner (not just Brand/Agency) ✅ Implemented
5. **Trust score system** - starts at 50, increases with verifications and successful transactions ✅ Implemented
6. **Duplicate upazila handling** - use "Name (District)" format for 28 ambiguous names ✅ Completed in Phase 1
7. **REST API for headless architecture** - All endpoints return JSON, support Next.js frontend ✅ Implemented
8. **Atomic registration** - User + Organization created in single transaction ✅ Implemented
9. **Multi-organization support** - Users can belong to multiple organizations and switch context ✅ Implemented

### Bangladesh Context Considerations:
- Phone verification is critical (often more reliable than email)
- Support bKash/Nagad mobile banking numbers for payment info
- Accept local business documents (Trade License, TIN Certificate)
- Multilingual support (English/Bengali) for all user-facing content
- SMS gateway for OTP (BD-SMS, SSL Wireless, or similar)

### Content Model Alignment:
- Organization ownership structure supports future content types:
  - Advertiser (brand/agency organization reference)
  - Billboard (owner organization reference)
  - Booking Inquiry (requester organization reference)
  - Billboard Cluster (owner organization reference)

---

## 🎓 Recommended Reading

~~Before implementing Phase 2~~:
- ✅ ~~Review [about_user.md](about_user.md) - detailed onboarding UX discussion~~
- ✅ ~~Review [Drupal_Content_Model_Sheet.md](Drupal_Content_Model_Sheet.md) - content architecture~~
- ✅ ~~Review billoria_core module services - understand existing business logic~~

**Phase 2 Complete! Now review:**
- **[application-wiki/](../application-wiki/)** - 20+ pages of comprehensive API documentation
- **[application-wiki/Organization-Schema.md](../application-wiki/Organization-Schema.md)** - All 35 organization fields
- **[application-wiki/REGISTRATION_API.md](../application-wiki/REGISTRATION_API.md)** - Registration flow and examples
- **[Drupal_Content_Model_Sheet.md](Drupal_Content_Model_Sheet.md)** - Billboard content type specification (next to implement)

---

## 🚀 Success Metrics (for MVP Launch)

### User Onboarding: ✅ IMPLEMENTED
- ✅ Target: <5 minutes to complete registration (API ready)
- ✅ Target: >70% email verification rate (email system ready)
- ⚠️ Target: >50% profile completion rate within 7 days (needs frontend + tracking)

### Content: 🔴 NEXT PHASE
- Target: 100+ verified billboards at launch
- Target: Coverage in 5+ major cities

### Transactions: ⏳ FUTURE
- Target: 10+ successful bookings in first month
- Target: <24 hour average response time to inquiries

---

**Next Action:** Start Phase 4.1 - Configure billboard search views with filters and faceted search
