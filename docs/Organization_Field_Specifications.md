# Organization Field Specifications

Complete reference of all fields in the `organization` content type (node bundle).

**Total Fields:** 38 custom fields + standard node fields (title, created, updated, etc.)

---

## Field Summary

| Field Label | Machine Name | Type | Cardinality | Required | Description |
|---|---|---|---|---|---|
| Organization Type | `field_org_type` | List | 1 | ❌ No | brand/agency/owner |
| Division | `field_division` | Entity Ref (Taxonomy) | Unlimited | ❌ No | Multiple division selections |
| District | `field_district` | Entity Ref (Taxonomy) | Unlimited | ❌ No | Multiple district selections |
| Official Email | `field_official_email` | Email | 1 | ✅ Yes | Valid email |
| Official Phone | `field_official_phone` | Telephone | 1 | ✅ Yes | +8801XXXXXXXXX format |
| Full Address | `field_full_address` | String | 1 | ✅ Yes | Max 500 chars |
| Business Registration Number | `field_business_reg_number` | String | 1 | ❌ No | Max 50 chars, unique |
| TIN | `field_tin` | String | 1 | ❌ No | Max 20 chars, unique |
| Website | `field_website` | Link | 1 | ❌ No | Max 255 chars |
| Logo | `field_org_logo` | Image | 1 | ❌ No | 2MB max, PNG/JPG |
| Establishment Year | `field_establishment_year` | Integer | 1 | ❌ No | 1900-2026 |
| Nationwide Service | `field_nationwide_service` | Boolean | 1 | ❌ No | True/False |
| International Service | `field_international_service` | Boolean | 1 | ❌ No | True/False |
| Verification Status | `field_verification_status` | List | 1 | ❌ No | pending/under_review/verified/rejected |
| Verification Documents | **`field_verification_docs`** | **File (Multi)** | **3** | ❌ No | **PDF/JPG/PNG, 5MB max each** |
| Trust Score | `field_trust_score` | Integer | 1 | Auto | 0-100 |
| Profile Completion | `field_profile_completion` | Integer | 1 | Auto | 0-100 |
| Postal Code | `field_postal_code` | String | 1 | ❌ No | Max 10 chars |
| City Corporation | `field_city_corporation` | Entity Ref (Taxonomy) | Unlimited | ❌ No | Urban governance areas |

---

## Agency-Specific Fields

| Field Label | Machine Name | Type | Cardinality | Required |
|---|---|---|---|---|
| Agency Services | `field_agency_services` | Taxonomy Ref | Unlimited | ❌ No |
| Portfolio Size | `field_portfolio_size` | String | 1 | ❌ No |
| Owns Inventory | `field_owns_inventory` | Boolean | 1 | ❌ No |
| Operations Contact | `field_operations_contact` | String | 1 | ❌ No |
| Finance Contact | `field_finance_contact` | String | 1 | ❌ No |
| Preferred Regions | `field_preferred_regions` | Entity Ref (Taxonomy) | Unlimited | ❌ No |
| Team Members | `field_team_members` | Entity Ref | Unlimited | ❌ No |

---

## Brand-Specific Fields

| Field Label | Machine Name | Type | Cardinality | Required |
|---|---|---|---|---|
| Industry Category | `field_industry_category` | Taxonomy Ref | 1 | ❌ No |
| Annual Budget Range | `field_annual_budget_range` | List | 1 | ❌ No |
| Booking Duration | `field_booking_duration` | List | 1 | ❌ No |
| Parent Company | `field_parent_company` | String | 1 | ❌ No |

---

## Owner-Specific Fields

| Field Label | Machine Name | Type | Cardinality | Required |
|---|---|---|---|---|
| Inventory Count | `field_inventory_count` | Integer | 1 | ❌ No |
| Total Coverage (sqft) | `field_total_coverage_sqft` | Decimal | 1 | ❌ No |
| Maintenance Capability | `field_maintenance_capability` | Boolean | 1 | ❌ No |
| Installation Services | `field_installation_services` | Boolean | 1 | ❌ No |
| Mobile Banking Enabled | `field_mobile_banking` | Boolean | 1 | ❌ No |

---

## Verification Documents Field Details

### Full Configuration

**Machine Name:** `field_verification_docs`  
**Label:** Verification Documents  
**Description:** Upload ownership/business documents (private)  
**Type:** File (multi-value)  
**Cardinality:** 3 files maximum  
**Required:** No  
**Allowed File Types:** PDF, JPG, JPEG, PNG  
**Max File Size:** 2 MB per file (updated from 5 MB)  
**Storage Location:** `/verification-docs/[YYYY]-[MM]/`  
**Description Field:** Enabled (users can add descriptions to files)  
**Translatable:** Yes  

### Use Cases

- Trade license certificates
- Tax registration documents
- Business registration proof
- Ownership verification documents
- Compliance documentation

### API Considerations

When retrieving organization data via API:
- Files are stored in the public file directory
- File references require `target_id` and optional metadata
- Use the `/api/v1/organization/{id}` endpoint to fetch
- Response includes: `url`, `filename`, and `description` (if provided)

When updating via API:
- POST multipart/form-data with file uploads
- Include file metadata (alt text, title, description)
- Validation occurs server-side
- Max file size: 2 MB per file

---

## Multi-Select Fields

The following fields support multiple values (Cardinality: Unlimited):

1. **`field_division`** — Multiple divisions
2. **`field_district`** — Multiple districts  
3. **`field_preferred_regions`** — Multiple preferred service regions
4. **`field_agency_services`** — Multiple services offered
5. **`field_city_corporation`** — Multiple city corporations
6. **`field_verification_docs`** — Up to 3 files

---

## Field Validation Rules

### Email Fields
- Pattern: RFC 5322
- Example: `info@company.com`

### Phone Fields
- Format: Bangladesh numbers only
- Pattern: `^\+8801[3-9]\d{8}$`
- Example: `+8801712345678`

### Integer Fields (Trust Score, Profile Completion)
- Range: 0-100

### File Fields (Verification Documents)
- Max size: 2 MB per file (updated from 5 MB)
- Allowed types: PDF, JPG, JPEG, PNG
- Max documents: 3
- Description field: Enabled (users can add descriptions)
- Description field: Enabled (users can add descriptions)

---

## Notes

- **Division & District as Arrays:** Both `field_division` and `field_district` support unlimited values. The latest changes updated the API to accept arrays (`divisions: [], districts: []`) instead of single values.
  
- **Verification Documents:** Already implemented and ready for use. No additional field creation needed.

- **Translatable Fields:** Many fields are marked as translatable for multilingual support (future feature).

- **Field Storage:** All field definitions are stored in Drupal's config system and can be exported via `ddev drush config:export`.
