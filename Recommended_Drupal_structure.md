Yes — let’s lock the **Drupal CMS content architecture** properly.

For this project, don’t put everything into content types.
Use a mix of:

* **Content types** for content-heavy records
* **Taxonomies** for controlled filters
* **Custom entities** for workflow-heavy business objects
* **User/profile fields** for account-specific data

That’ll keep the system clean and scalable.

---

# Recommended Drupal structure

## 1. Content Types

These are the main content types I’d create first.

### A. Billboard

This is the core inventory object.

**Purpose:** store a billboard listing that brands can discover and request.

**Suggested fields**

* Title
* Billboard Code
* Owner Reference
* Agency Reference
* District
* Area / Zone
* Full Address
* Map Coordinates
* Landmark
* Billboard Type
* Width
* Height
* Unit
* Facing Direction
* Road Type
* Illumination Type
* Visibility Notes
* Traffic Notes
* Availability Status
* Verification Status
* Base Price
* Pricing Model
* Minimum Booking Duration
* Media Gallery
* Legal Notes
* Active / Inactive
* Published / Unpublished

---

### B. Agency Profile

**Purpose:** public/admin profile for billboard agencies.

**Suggested fields**

* Agency Name
* Logo
* Contact Person
* Phone
* Email
* Office Address
* Service Areas
* Trade License
* Website
* Verification Status
* Agency Description

---

### C. Owner Profile

Use this only if direct owners need profile pages separate from user fields.

**Suggested fields**

* Owner / Company Name
* Contact Person
* Phone
* Email
* Address
* NID / Trade License Ref
* Owned Billboard Count
* Verification Status

If owner data is simple, this can be user profile fields instead of a full content type.

---

### D. Vendor Profile

For future tender/bid workflow.

**Suggested fields**

* Vendor Name
* Vendor Type
* Services Offered
* Portfolio
* Coverage Area
* Contact Info
* Verification Status
* Rating Summary
* Tax / legal info references

---

### E. Knowledge / Legal Guide

Optional but useful later.

**Purpose:** store legal guides, tax instructions, paperwork templates, help docs.

**Suggested fields**

* Title
* Category
* Description
* File attachment
* Related region
* Applicable role

---

# 2. Taxonomies

Use taxonomy for anything that should be reusable in filters.

## Recommended vocabularies

### Billboard Type

* Static
* Digital
* Rooftop
* Gantry
* Wall Sign
* Highway Board

### District

All districts or a location hierarchy starter.

### Area / Zone

Subareas inside districts/cities.

### Pricing Model

* Daily
* Weekly
* Monthly
* Campaign Based

### Illumination Type

* Frontlit
* Backlit
* Non-lit
* LED

### Road Type

* Highway
* Major Road
* Urban Street
* Market Area

### Vendor Service Type

* Design
* Print
* Installation
* Maintenance

### Verification Status

This can be taxonomy or workflow field, but I’d usually keep it as a controlled field/workflow value instead.

---

# 3. Better as Custom Entities, not Content Types

These are workflow objects. I would **not** start them as normal nodes unless you want a very fast prototype.

## A. Booking Request

**Why custom entity:** status, ownership, validation, permissions, business logic.

**Fields**

* Billboard Reference
* Brand User Reference
* Requested Start Date
* Requested End Date
* Campaign Type
* Notes
* Requested Budget
* Status
* Assigned Agency / Owner
* Internal Comments
* Final Agreed Price

---

## B. Negotiation / Deal

If you want structured negotiation later.

**Fields**

* Booking Reference
* Participants
* Offer Amount
* Counter Offer
* Status
* Notes
* Attachment refs

---

## C. Tender / Bid

For vendor marketplace phase.

**Fields**

* Related Campaign / Booking
* Vendor Reference
* Bid Amount
* Delivery Time
* Proposal Notes
* Attachment
* Status

---

## D. Payment Tracking

Later phase.

**Fields**

* Related Booking
* Amount
* Due Date
* Payment Type
* Status
* Transaction Reference
* Invoice File

---

## E. Compliance / Tax Record

Later phase.

**Fields**

* Related Booking / Party
* Tax Type
* Amount
* Document Ref
* Status
* Notes

---

# 4. User Roles + Profile Data

Don’t create a content type for everything if the info belongs to the logged-in account.

## User roles

* platform_admin
* billboard_owner
* agency
* brand_user
* vendor

## User fields

Add these on the User entity if needed:

* Full Name
* Company Name
* Phone
* Account Type
* Verification Status
* Address
* Trade License Number
* NID / Business Doc Ref
* Profile Completion Status

For richer profile pages, later you can still create profile entities.

---

# Best MVP recommendation

For the first version, I’d build only these CMS structures:

## Content Types

* Billboard
* Agency Profile
* Vendor Profile

## Taxonomies

* Billboard Type
* District
* Area / Zone
* Pricing Model
* Road Type
* Illumination Type

## User fields

* company_name
* phone
* account_type
* verification_status

## Custom entities

* Booking Request

That is enough to start strong.

---

# Suggested Billboard field machine names

For Drupal cleanliness, use a stable naming style:

* `field_billboard_code`
* `field_owner`
* `field_agency`
* `field_district`
* `field_area`
* `field_address`
* `field_coordinates`
* `field_landmark`
* `field_billboard_type`
* `field_width`
* `field_height`
* `field_unit`
* `field_facing_direction`
* `field_road_type`
* `field_illumination_type`
* `field_visibility_notes`
* `field_traffic_notes`
* `field_availability_status`
* `field_verification_status`
* `field_base_price`
* `field_pricing_model`
* `field_min_booking_duration`
* `field_gallery`
* `field_legal_notes`

---

# Publishing workflow suggestion

For **Billboard** content type, use moderation:

* Draft
* Pending Review
* Verified
* Rejected
* Archived

This helps because inventory quality is everything.

---

# Important modeling advice

## Do not do this

* make bookings as normal page content forever
* put tax logic into random text fields
* store coordinates as plain text only
* use free-text everywhere instead of taxonomy/reference fields

## Do this

* structured references
* moderation states
* controlled vocabularies
* custom entities for business workflows

---

# My recommendation in one line

**Billboard = content type**
**Booking = custom entity**
**Filters = taxonomy**
**Account metadata = user fields**

That’s the right Drupal shape for this product.

I can do the next layer now: a **full Drupal content model sheet** with field names, field types, references, and which ones belong in MVP vs Phase 2.
