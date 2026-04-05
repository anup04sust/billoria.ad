Yes — that’s a smart onboarding direction.

What you want is basically:

**First user = account owner / primary admin**
That person creates the organization account as either:

* **Agency**
* **Brand**

Then that person becomes responsible for:

* organization profile completion
* brand/agency business data
* contact person details
* ensuring the email is valid and verified

For your billboard platform, I’d structure it like this:

## Simple onboarding model

### Step 1: Choose account type

First screen:

* I am a **Brand**
* I am an **Agency**

This choice controls which fields appear later.

---

### Step 2: Create primary account

Ask only the minimum:

* Full name
* Work email
* Password / OTP login
* Mobile number
* Checkbox: “I am authorized to manage this organization”

Important rule:

* personal/free emails can be allowed for MVP
* but **work email verification should be strongly encouraged**
* later you can mark account status:

  * Unverified
  * Email verified
  * Business verified

---

### Step 3: Verify email first

Before letting them fully use the platform:

* send OTP or verification link
* only verified email can become **Primary Admin**

This solves your main concern:
the person responsible for org data must have a real email they control.

---

### Step 4: Organization profile setup

After email verification:

Common fields for both:

* Organization name
* Organization type: Brand / Agency
* Official email
* Phone
* Website
* Address
* City
* District
* Business logo
* Trade license / business document optional for MVP
* Billing contact name
* Billing email
* Accounts contact phone

Extra for **Brand**:

* Industry / category
* Preferred campaign regions
* Typical booking budget range
* Brand billing name
* GST/VAT/TIN style business fields if needed later

Extra for **Agency**:

* Agency service type
* Number of clients handled
* Coverage area
* Do you own inventory or manage third-party inventory?
* Operations contact
* Finance contact

---

## Best role structure

Keep it very simple at first:

### 1. Primary Admin

The first user

* owns the org account
* can edit all org data
* can invite team members
* can approve profile/business details
* can change official email
* can manage brand/agency fields

### 2. Team Member

Added later by Primary Admin

* can work on listings, inquiries, bookings
* cannot change core business identity unless permitted

### 3. Finance / Billing Manager

Optional later

* billing, invoice, payment follow-up only

For MVP, even just these two are enough:

* Primary Admin
* Team Member

---

## Important data ownership rule

You should treat the first verified user as:

**“Responsible Contact”**

Store these separate fields:

### User fields

* first_name
* last_name
* login_email
* mobile
* email_verified
* last_login
* role

### Organization fields

* org_name
* org_type
* official_email
* official_phone
* address
* business_status
* profile_completion_percent

### Responsible contact fields

* responsible_person_name
* responsible_person_designation
* responsible_person_email
* responsible_person_phone
* same_as_login_user? yes/no

This is useful because sometimes:

* the user account email
* and the official company email
  are not the same.

Example:

* login email: `rakib.agency@gmail.com`
* official business email: `booking@xyzmedia.com`

That’s normal. Just verify the login email first, then optionally verify official business email too.

---

## Verification levels you can use

This will make the system clean:

### Level 1 — Account verified

* login email verified

### Level 2 — Organization email verified

* official company email verified

### Level 3 — Business verified

* admin reviewed documents or company proof

This gives trust signals later on the marketplace.

Example badges:

* Email Verified
* Organization Verified
* Business Verified

That will matter a lot when brands and agencies deal with each other.

---

## Recommended onboarding flow

A clean flow could be:

1. Select Brand or Agency
2. Create account
3. Verify email
4. Complete organization profile
5. Add official contact info
6. Review and submit
7. Enter dashboard

Then inside dashboard show:

**Profile completion**

* Email verified ✅
* Company info completed ✅
* Contact details pending
* Logo missing
* Official email not verified

This keeps onboarding easy without forcing everything in one long form.

---

## Very important UX idea

Do **not** ask for too many fields before activation.

Best approach:

### At signup:

* Name
* Email
* Password / OTP
* Account type

### After signup:

* organization details
* business info
* agency/brand-specific fields

That reduces drop-off.

---

## Suggested rules for trust and control

You should add these business rules:

* first verified user becomes **Primary Admin**
* only Primary Admin can transfer ownership
* only verified Primary Admin can invite others
* organization cannot publish listings / request bookings until minimum profile is complete
* booking inquiries may require at least:

  * verified email
  * phone
  * organization name

---

## Good Drupal-style structure

For Drupal, this can be handled like:

### User entity

Stores:

* login credentials
* user role
* personal contact info
* verification status

### Organization content/entity

Stores:

* brand or agency profile
* business fields
* official contacts
* verification flags

### Relationship

* one organization has many users
* one user is marked as primary admin / owner

This is better than putting all agency/brand data directly into the user profile.

---

## My recommendation for your MVP

Use this exact logic:

**User signs up → chooses Brand/Agency → verifies email → creates organization profile → becomes Primary Admin**

And keep these statuses:

* Draft
* Email Verified
* Profile Completed
* Verified Business

That’s simple, scalable, and easy to understand.

## Suggested labels

Instead of “first user”, use one of these:

* Primary Admin
* Account Owner
* Organization Owner
* Responsible Contact

Best one for UI:
**Primary Admin**

Best one for backend/business logic:
**Responsible Contact**

---

## Very short product rule

You can define it like this:

> The first registered and email-verified user for a Brand or Agency account becomes the Primary Admin and is responsible for maintaining accurate organization profile data, official contact details, and team access.


