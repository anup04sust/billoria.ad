# User Onboarding - Implementation Specification

**Reference:** [about_user.md](about_user.md) - Original onboarding discussion  
**Roadmap:** [PROJECT_ROADMAP.md](PROJECT_ROADMAP.md) - Phase 2 details  
**Status:** 📋 Ready for implementation

---

## 🎯 Key Enhancements to Original Proposal

### Critical Addition: "Owner" Account Type ⭐
The original proposal covered Brand/Agency but **missed Billboard Owners** - the supply side of the marketplace.

**Why this is critical:**
- Without owners, there's no inventory to book
- Owners need stricter verification (ownership proof)
- Different permissions and workflows
- Revenue model depends on owner participation

### Enhanced from Original:
1. ✅ Added "Owner" as third account type
2. ✅ Dual verification (email + phone) - phone is critical in BD context
3. ✅ Trust score system (50-100) with progressive access
4. ✅ Multi-organization support architecture
5. ✅ Owner-specific approval workflow

---

## 🏗️ Drupal Implementation Architecture

### Entity Structure

```
User Entity (core Drupal)
├─ field_mobile_number: tel (required)
├─ field_designation: string
├─ field_email_verified: boolean
├─ field_phone_verified: boolean
├─ field_verification_token: string (stores OTP/token)
├─ field_token_expiry: timestamp
├─ field_organizations: entity_ref[] (multi-org support)
├─ field_active_organization: entity_ref (current context)
└─ field_is_primary_admin_of: entity_ref[] (orgs where user is admin)

Organization Node Type (content/node/organization)
├─ title: Organization Name
├─ field_org_type: list (brand|agency|owner)
├─ field_primary_admin: entity_ref → user
├─ field_team_members: entity_ref[] → users
├─ field_official_email: email
├─ field_official_phone: tel
├─ field_mobile_banking: tel (bKash/Nagad)
├─ field_website: link
├─ field_address: composite (division, district, upazila, city_corp, full_address)
├─ field_business_reg_number: string
├─ field_tin: string
├─ field_establishment_year: integer
├─ field_logo: image (public)
├─ field_verification_docs: file[] (private - ownership/trade license)
├─ field_verification_status: list (draft|pending|verified|suspended)
├─ field_trust_score: integer (0-100, computed)
├─ field_profile_completion: integer (0-100, computed)
├─ Type-specific fields:
│   ├─ Brand: industry, budget_range, regions, parent_company
│   ├─ Agency: services[], portfolio_size, specialization, coverage
│   └─ Owner: inventory_count, maintenance, installation_services, coverage_districts
└─ Timestamps: created, changed, verified_date
```

### Relationships

```
User ─(member_of)─> Organization(s) ─(owns)─> Billboard(s)
  │                      │
  │                      └─(creates)─> Booking Inquiries
  └─(primary_admin_of)─> Organization(s)
```

---

## 📝 Step-by-Step Implementation Tasks

### Task 1: Create Organization Content Type (2 hours)

**Script:** `scripts/create-organization-content-type.php`

```php
<?php
use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

// Create content type
$type = NodeType::create([
  'type' => 'organization',
  'name' => 'Organization',
  'description' => 'Brand, Agency, or Billboard Owner profile',
]);
$type->save();

// Add field_org_type
$field_storage = FieldStorageConfig::create([
  'field_name' => 'field_org_type',
  'entity_type' => 'node',
  'type' => 'list_string',
  'cardinality' => 1,
  'settings' => [
    'allowed_values' => [
      'brand' => 'Brand/Advertiser',
      'agency' => 'Advertising Agency',
      'owner' => 'Billboard Owner',
    ],
  ],
]);
$field_storage->save();

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'organization',
  'label' => 'Organization Type',
  'required' => TRUE,
]);
$field->save();

// Add field_official_email
$field_storage = FieldStorageConfig::create([
  'field_name' => 'field_official_email',
  'entity_type' => 'node',
  'type' => 'email',
]);
$field_storage->save();

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'organization',
  'label' => 'Official Email',
  'required' => TRUE,
]);
$field->save();

// Add field_official_phone (telephone)
// Add field_primary_admin (entity_reference to user)
// Add field_team_members (entity_reference multiple to users)
// Add field_verification_status
// Add field_trust_score
// Add field_profile_completion
// ... (continue for all 30+ fields)
```

**Checklist:**
- [ ] Run script to create Organization content type
- [ ] Verify all fields created
- [ ] Configure form display (3 variants: brand, agency, owner)
- [ ] Configure view display (teaser, full)
- [ ] Set permissions

---

### Task 2: Extend User Entity (1 hour)

**Script:** `scripts/add-user-verification-fields.php`

```php
<?php
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

// Add mobile number field
$field_storage = FieldStorageConfig::create([
  'field_name' => 'field_mobile_number',
  'entity_type' => 'user',
  'type' => 'telephone',
  'cardinality' => 1,
]);
$field_storage->save();

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'user',
  'label' => 'Mobile Number',
  'required' => TRUE,
  'description' => 'Required for booking confirmations and OTP verification',
]);
$field->save();

// Add email_verified boolean
$field_storage = FieldStorageConfig::create([
  'field_name' => 'field_email_verified',
  'entity_type' => 'user',
  'type' => 'boolean',
  'cardinality' => 1,
]);
$field_storage->save();

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'user',
  'label' => 'Email Verified',
  'default_value' => [['value' => 0]],
]);
$field->save();

// Add organization entity reference
$field_storage = FieldStorageConfig::create([
  'field_name' => 'field_organization',
  'entity_type' => 'user',
  'type' => 'entity_reference',
  'cardinality' => -1, // Unlimited (multi-org support)
  'settings' => [
    'target_type' => 'node',
  ],
]);
$field_storage->save();

$field = FieldConfig::create([
  'field_storage' => $field_storage,
  'bundle' => 'user',
  'label' => 'Organizations',
  'settings' => [
    'handler' => 'default:node',
    'handler_settings' => [
      'target_bundles' => ['organization' => 'organization'],
    ],
  ],
]);
$field->save();

// Continue for all user fields...
```

**Checklist:**
- [ ] Create and run script
- [ ] Verify fields on user entity
- [ ] Configure user registration form display
- [ ] Update user edit form display

---

### Task 3: Registration Forms (3-4 hours)

**File:** `src/Form/BrandRegistrationForm.php`

Complete multi-step form implementation with:
- State management across steps
- Validation at each step
- Conditional fields based on account type
- AJAX for district → upazila dependent dropdown
- User creation + Organization node creation
- Email verification trigger

**Checklist:**
- [ ] Create BrandRegistrationForm.php
- [ ] Create AgencyRegistrationForm.php  
- [ ] Create OwnerRegistrationForm.php
- [ ] Create base AbstractRegistrationForm.php (shared logic)
- [ ] Add AJAX callbacks for dependent dropdowns
- [ ] Implement form state storage
- [ ] Add validation handlers
- [ ] Test all 3 registration flows

---

### Task 4: Verification Service Enhancement (2-3 hours)

**File:** `src/Service/VerificationService.php` (already exists, enhance it)

Add methods:
- `generateEmailVerificationToken()`
- `verifyEmailToken()`
- `generatePhoneOTP()`
- `verifyPhoneOTP()`
- `sendVerificationEmail()`
- `sendVerificationSMS()`
- `updateTrustScore()`
- `calculateProfileCompletion()`
- `checkAccessLevel()` (determines what user can do)

**SMS Gateway Integration:**
```php
protected function sendSMS($mobile, $message) {
  // Option 1: BD-SMS API
  $endpoint = 'https://api.bdsms.net/sms/send';
  $params = [
    'api_key' => getenv('BDSMS_API_KEY'),
    'number' => $mobile,
    'message' => $message,
  ];
  
  // Option 2: SSL Wireless
  // Option 3: Store in queue for later sending (MVP)
  
  $response = $this->httpClient->post($endpoint, ['json' => $params]);
  return $response->getStatusCode() === 200;
}
```

**Checklist:**
- [ ] Extend VerificationService class
- [ ] Add email token generation/validation
- [ ] Add OTP generation/validation
- [ ] Integrate SMS gateway (or stub for MVP)
- [ ] Implement trust score calculation
- [ ] Add profile completion calculator
- [ ] Test verification flows

---

### Task 5: Verification Controllers & Routes (2 hours)

**File:** `src/Controller/VerificationController.php`

```php
<?php

namespace Drupal\billoria_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class VerificationController extends ControllerBase {

  /**
   * Email verification endpoint
   */
  public function verifyEmail($token) {
    $service = \Drupal::service('billoria_core.verification');
    $result = $service->verifyEmailToken($token);
    
    if ($result['success']) {
      $this->messenger()->addStatus('Email verified successfully! You can now log in.');
      return $this->redirect('user.login');
    } else {
      $this->messenger()->addError($result['error']);
      return $this->redirect('billoria_core.verify_email_instructions');
    }
  }
  
  /**
   * Send OTP for phone verification
   */
  public function sendPhoneOTP(Request $request) {
    $user = $this->currentUser();
    if (!$user->id()) {
      return new JsonResponse(['error' => 'Not authenticated'], 401);
    }
    
    $service = \Drupal::service('billoria_core.verification');
    $service->generatePhoneOTP($user->id());
    
    return new JsonResponse(['success' => TRUE, 'message' => 'OTP sent to your mobile']);
  }
  
  /**
   * Verify phone OTP
   */
  public function verifyPhoneOTP(Request $request) {
    $user = $this->currentUser();
    $otp = $request->request->get('otp');
    
    $service = \Drupal::service('billoria_core.verification');
    $result = $service->verifyPhoneOTP($user->id(), $otp);
    
    return new JsonResponse($result);
  }
}
```

**Checklist:**
- [ ] Create VerificationController
- [ ] Add all verification endpoints
- [ ] Create verification instruction pages
- [ ] Add success/error messaging
- [ ] Test token/OTP flows

---

### Task 6: Dashboard & Profile Widget (3 hours)

**File:** `src/Plugin/Block/ProfileCompletionBlock.php`

```php
<?php

namespace Drupal\billoria_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Profile Completion block.
 *
 * @Block(
 *   id = "billoria_profile_completion",
 *   admin_label = @Translation("Profile Completion Widget"),
 * )
 */
class ProfileCompletionBlock extends BlockBase {

  public function build() {
    $user = \Drupal::currentUser();
    $account = \Drupal\user\Entity\User::load($user->id());
    
    // Get active organization
    $org_id = $account->get('field_active_organization')->target_id;
    if (!$org_id) {
      return ['#markup' => 'No organization found'];
    }
    
    $org = \Drupal\node\Entity\Node::load($org_id);
    
    // Calculate completion
    $service = \Drupal::service('billoria_core.verification');
    $completion = $service->calculateProfileCompletion($org);
    $trust_score = $org->get('field_trust_score')->value ?? 50;
    
    // Get verification status checklist
    $checklist = [
      [
        'label' => 'Email verified',
        'completed' => $account->get('field_email_verified')->value,
        'action' => '/verify-email',
      ],
      [
        'label' => 'Phone verification',
        'completed' => $account->get('field_phone_verified')->value,
        'action' => '/verify-phone',
      ],
      [
        'label' => 'Organization details',
        'completed' => !$org->get('field_official_email')->isEmpty(),
        'action' => '/organization/edit',
      ],
      [
        'label' => 'Business documents',
        'completed' => !$org->get('field_verification_docs')->isEmpty(),
        'action' => '/organization/upload-documents',
      ],
      [
        'label' => 'Logo uploaded',
        'completed' => !$org->get('field_logo')->isEmpty(),
        'action' => '/organization/upload-logo',
      ],
    ];
    
    return [
      '#theme' => 'billoria_profile_completion',
      '#completion' => $completion,
      '#trust_score' => $trust_score,
      '#checklist' => $checklist,
      '#org_type' => $org->get('field_org_type')->value,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['user:' . $user->id(), 'node:' . $org_id],
      ],
    ];
  }
}
```

**Template:** `templates/billoria-profile-completion.html.twig`

```twig
<div class="profile-completion-widget">
  <div class="completion-header">
    <h3>{{ 'Profile Completion'|t }}</h3>
    <div class="completion-percentage">{{ completion }}%</div>
  </div>
  
  <div class="progress-bar">
    <div class="progress-fill" style="width: {{ completion }}%"></div>
  </div>
  
  <div class="trust-score">
    <span class="label">{{ 'Trust Score'|t }}:</span>
    <span class="score">{{ trust_score }}/100</span>
  </div>
  
  <ul class="checklist">
    {% for item in checklist %}
      <li class="checklist-item {{ item.completed ? 'completed' : 'pending' }}">
        <span class="icon">{{ item.completed ? '✅' : '⚠️' }}</span>
        <span class="label">{{ item.label|t }}</span>
        {% if not item.completed %}
          <a href="{{ item.action }}" class="action-link">{{ 'Complete'|t }} →</a>
        {% endif %}
      </li>
    {% endfor %}
  </ul>
  
  <div class="completion-benefits">
    <h4>{{ 'Why complete your profile?'|t }}</h4>
    <ul>
      <li>{{ 'Increase credibility with verified badges'|t }}</li>
      <li>{{ 'Access exclusive billboard inventory'|t }}</li>
      <li>{{ 'Get faster responses to inquiries'|t }}</li>
    </ul>
  </div>
  
  <a href="/organization/complete-profile" class="btn-primary">
    {{ 'Complete Profile'|t }}
  </a>
</div>
```

**Checklist:**
- [ ] Create ProfileCompletionBlock plugin
- [ ] Create Twig template
- [ ] Add CSS styling
- [ ] Place block in user dashboard region
- [ ] Test completion calculation

---

### Task 7: Access Control Service (2 hours)

**File:** `src/Service/AccessControlService.php`

Implement access control matrix checking:
- Can user browse billboards? (yes if email verified)
- Can user request booking? (yes if phone verified)
- Can owner publish billboard? (yes if business verified)
- Check concurrent inquiry limits
- Enforce verification gates

**Checklist:**
- [ ] Create AccessControlService
- [ ] Implement access check methods
- [ ] Add to billoria_core.services.yml
- [ ] Integrate with booking forms
- [ ] Integrate with billboard creation
- [ ] Test all access scenarios

---

### Task 8: Admin Verification Queue (2-3 hours)

**View:** Create custom view for pending verifications

**Path:** `/admin/billoria/verifications/pending`

**Display:** Table showing:
- Organization name
- Type (Brand/Agency/Owner)
- Registered date
- Documents uploaded
- Trust score
- Actions: [Approve] [Reject] [View Details]

**Form:** Approval/Rejection form with:
- Approval checkbox
- Rejection reason (if rejected)
- Internal notes
- Send notification checkbox

**Checklist:**
- [ ] Create admin verification view
- [ ] Add approval form
- [ ] Implement approval/rejection logic
- [ ] Send notification emails on status change
- [ ] Create audit log
- [ ] Test workflow

---

## 🎪 Registration Flow Examples

### Example 1: Brand Registration

```
POST /register/brand

Step 1 Data:
{
  "full_name": "Rakib Ahmed",
  "email": "rakib@example.com",
  "mobile": "+8801712345678",
  "password": "SecurePass123!",
  "terms_agreed": true
}

→ User created (status=0, email_verified=false)
→ Verification email sent
→ Redirect to /verify-email

Step 2: Email Verification
{
  "token": "eyJhbGc..."
}

→ User status=1, email_verified=true
→ Redirect to /register/brand/organization

Step 3: Organization Setup
{
  "org_name": "Fresh Foods Ltd",
  "official_email": "info@freshfoods.com",
  "official_phone": "+88029876543",
  "website": "https://freshfoods.com",
  "business_reg": "C-12345",
  "tin": "TIN-67890"
}

→ Organization node created
→ User linked as primary_admin
→ Redirect to /register/brand/profile

Step 4: Brand Profile
{
  "industry": "FMCG",
  "budget_range": "5-20L",
  "preferred_regions": ["dhaka", "chattogram"],
  "logo": [file upload]
}

→ Brand-specific fields saved
→ Profile_completion calculated: 75%
→ Redirect to /dashboard
```

### Example 2: Owner Registration (with approval)

```
Similar flow as Brand, but:

Step 4: Owner Verification
{
  "inventory_count": 25,
  "coverage_districts": ["dhaka", "gazipur", "narayanganj"],
  "maintenance": "own_team",
  "installation_services": true,
  "ownership_docs": [PDF uploads - trade license, property deed]
}

→ Owner fields saved
→ field_verification_status = "pending_admin_approval"
→ Email sent to admin team
→ User sees: "Your application is under review"
→ Dashboard shows: "Pending Verification" badge

Admin approves:
→ field_verification_status = "business_verified"
→ Trust score += 15 → now 80/100
→ Email notification to owner
→ Owner can now add billboards
```

---

## 📧 Notification Templates

### 1. Email Verification
```
Subject: Verify your Billoria account

Hi {{ user.full_name }},

Click to verify: {{ verification_link }}
Or enter code: {{ otp_code }}

Expires in 1 hour.
```

### 2. Welcome After Verification
```
Subject: Welcome to Billoria! 🎉

Hi {{ user.full_name }},

Your email is verified. Complete your profile:
- Add phone number [Link]
- Upload business docs [Link]
- Add organization logo [Link]

Profile: {{ completion }}% complete
```

### 3. Owner Approval Notification
```
Subject: Your Billoria Owner Account is Approved! 🎊

Hi {{ user.full_name }},

Great news! Your billboard owner account for {{ org.name }} has been verified.

You can now:
✓ Add your billboard inventory
✓ Receive booking inquiries
✓ Set your own rates

Start adding: {{ add_billboard_link }}

Welcome to Billoria!
```

### 4. Owner Rejection Notification  
```
Subject: Additional Information Needed for Verification

Hi {{ user.full_name }},

We need additional information to verify {{ org.name }}:

Reason: {{ rejection_reason }}

Please:
1. Upload clear ownership documents
2. Ensure business registration is valid
3. Resubmit for review

Update profile: {{ profile_link }}

Questions? Reply to this email.
```

---

## 🎨 UI/UX Components Needed

### React Components (for Next.js frontend):

**Registration:**
- `<AccountTypeSelector />` - Visual cards for brand/agency/owner
- `<RegistrationForm />` - Multi-step container
- `<StepIndicator />` - Progress dots
- `<EmailVerification />` - OTP input component
- `<PhoneVerification />` - OTP input component
- `<OrganizationForm />` - Common org fields
- `<BrandProfileForm />` - Brand-specific fields
- `<AgencyProfileForm />` - Agency-specific
- `<OwnerProfileForm />` - Owner-specific

**Dashboard:**
- `<ProfileCompletionWidget />` - Shows percentage + checklist
- `<VerificationBadges />` - Email/Phone/Business badges
- `<TrustScoreDisplay />` - Circular progress indicator
- `<OrganizationSwitcher />` - Dropdown for multi-org users

---

## ✅ Definition of Done

Phase 2 is complete when:

- [ ] All 3 account types can register successfully
- [ ] Email verification working end-to-end
- [ ] Phone verification working (or stubbed with plan)
- [ ] Organization profiles created and editable
- [ ] Profile completion calculated correctly
- [ ] Trust score updates properly
- [ ] Access control enforced (brands can't add billboards, owners can't book)
- [ ] Admin can approve/reject owner applications
- [ ] Dashboard shows profile completion widget
- [ ] Verification badges display on profiles
- [ ] All forms have proper validation
- [ ] Mobile-responsive UI
- [ ] Bengali translations for all forms
- [ ] 10+ test accounts created (brands, agencies, owners)

---

## 🚀 Launch Checklist

Before going live:

- [ ] Test all 3 registration flows on staging
- [ ] Verify email sending works (configure SMTP)
- [ ] SMS gateway integrated or stub messaging shown
- [ ] Admin verification queue tested
- [ ] Permission checks validated
- [ ] Performance tested (100+ concurrent registrations)
- [ ] Security audit (SQL injection, XSS, CSRF)
- [ ] GDPR/privacy compliance (data encryption, retention policy)
- [ ] Backup strategy for user data
- [ ] Monitoring alerts set up

---

**Next Action:** Create Organization content type with script

**Reference Files:**
- [about_user.md](about_user.md) - Original onboarding UX discussion
- [Drupal_Content_Model_Sheet.md](Drupal_Content_Model_Sheet.md) - Full content specs
- [PROJECT_ROADMAP.md](PROJECT_ROADMAP.md) - Project timeline
