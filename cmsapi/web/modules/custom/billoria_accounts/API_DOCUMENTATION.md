# Billoria Onboarding API Documentation

This document describes the REST API endpoints for user registration and onboarding in the Billoria platform.

## Base URL
- **Development**: `http://billoria.ad.ddev.site`
- **Production**: `https://api.billoria.ad`

## Authentication
Most endpoints use cookie-based authentication. After registration, use Drupal's built-in login endpoint.

---

## Endpoints

### 1. Register User with Organization

**POST** `/api/register`

Create a new user account and organization profile.

**Request Body:**
```json
{
  "accountType": "brand",  // or "agency" or "owner"
  "user": {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "mobileNumber": "+8801712345678"
  },
  "organization": {
    "name": "Acme Corporation",
    "officialEmail": "info@acme.com",
    "officialPhone": "+8801712345678",
    "website": "https://acme.com",
    "division": 1,  // taxonomy term ID
    "district": 10,
    "cityCorporation": 5,  // optional
    "fullAddress": "123 Main Street, Dhaka 1000",
    "postalCode": "1000",
    "businessRegNumber": "REG-123456",  // optional
    "tin": "TIN-789012",  // optional
    "establishmentYear": 2020,  // optional

    // Brand-specific (only if accountType="brand")
    "parentCompany": "Global Brands Inc",
    "annualBudgetRange": "20l_50l",
    "bookingDuration": "seasonal",
    "preferredRegions": [1, 2, 3],  // division term IDs

    // Agency-specific (only if accountType="agency")
    "agencyServices": ["media_planning", "ooh", "digital"],
    "portfolioSize": "medium",
    "ownsInventory": false,
    "operationsContact": "Jane Doe, +8801712345679",
    "financeContact": "Bob Smith, +8801712345680",
    "preferredRegions": [1, 2],

    // Owner-specific (only if accountType="owner")
    "inventoryCount": 25,
    "maintenanceCapability": "own_team",
    "installationServices": true,
    "coverageDistricts": [10, 11, 12]  // district term IDs
  }
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful. Please check your email for verification.",
  "data": {
    "userId": 123,
    "organizationId": 456,
    "email": "john@example.com",
    "verificationRequired": true
  }
}
```

**Error Responses:**
- `400`: Missing required fields or invalid data
- `409`: Email already registered

---

### 2. Verify Email

**POST** `/api/verify-email`

Verify user email with token from email.

**Request Body:**
```json
{
  "uid": 123,
  "token": "a1b2c3d4e5f6..."
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Email verified successfully",
  "data": {
    "userId": 123,
    "emailVerified": true,
    "trustScore": 60
  }
}
```

**Error Responses:**
- `400`: Missing uid or token
- `401`: Invalid or expired token
- `404`: User not found

---

### 3. Request Phone OTP

**POST** `/api/request-phone-otp`

**Requires Authentication**

Request OTP for phone verification.

**Success Response (200):**
```json
{
  "success": true,
  "message": "OTP sent to your mobile number",
  "data": {
    "mobile": "+880171234XXXX",
    "expiresIn": 600
  }
}
```

---

### 4. Verify Phone with OTP

**POST** `/api/verify-phone`

**Requires Authentication**

Verify phone number with OTP.

**Request Body:**
```json
{
  "otp": "123456"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Phone verified successfully",
  "data": {
    "phoneVerified": true,
    "trustScore": 75
  }
}
```

**Error Responses:**
- `400`: Missing OTP
- `401`: Invalid or expired OTP
- `404`: User not found

---

### 5. Resend Verification Email

**POST** `/api/resend-verification`

**Requires Authentication**

Request a new verification email.

**Success Response (200):**
```json
{
  "success": true,
  "message": "Verification email sent"
}
```

---

### 6. Get User Profile

**GET** `/api/user/profile`

**Requires Authentication**

Get current user profile with organization data.

**Success Response (200):**
```json
{
  "user": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "mobileNumber": "+8801712345678",
    "designation": "Marketing Manager",
    "emailVerified": true,
    "phoneVerified": true,
    "isPrimaryAdmin": true
  },
  "organization": {
    "id": 456,
    "name": "Acme Corporation",
    "type": "brand",
    "officialEmail": "info@acme.com",
    "officialPhone": "+8801712345678",
    "website": "https://acme.com",
    "division": 1,
    "district": 10,
    "fullAddress": "123 Main Street, Dhaka 1000",
    "verificationStatus": "email_verified",
    "trustScore": 75,
    "profileCompletion": 60,
    "brandDetails": {
      "parentCompany": "Global Brands Inc",
      "annualBudgetRange": "20l_50l",
      "bookingDuration": "seasonal",
      "preferredRegions": [1, 2, 3]
    }
  },
  "verificationStatus": {
    "emailVerified": true,
    "phoneVerified": true,
    "businessVerified": false,
    "trustScore": 75
  },
  "profileCompletion": 60
}
```

---

### 7. Get Organization Status

**GET** `/api/organization/{nid}/status`

**Requires Authentication**

Get organization status and statistics.

**Success Response (200):**
```json
{
  "id": 456,
  "name": "Acme Corporation",
  "type": "brand",
  "verificationStatus": "email_verified",
  "trustScore": 75,
  "profileCompletion": 60,
  "stats": {}
}
```

For owners:
```json
{
  "stats": {
    "inventoryCount": 25,
    "coverageSqft": 15000.50
  }
}
```

---

## Next.js Integration

### Install Dependencies

```bash
npm install axios
# or
yarn add axios
```

### API Client Setup

Create `lib/api/auth.ts`:

```typescript
import axios from 'axios';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://billoria.ad.ddev.site';

export const authAPI = {
  register: async (data: {
    accountType: 'brand' | 'agency' | 'owner';
    user: {
      name: string;
      email: string;
      password: string;
      mobileNumber: string;
    };
    organization: any;
  }) => {
    const response = await axios.post(`${API_BASE}/api/register`, data);
    return response.data;
  },

  verifyEmail: async (uid: number, token: string) => {
    const response = await axios.post(`${API_BASE}/api/verify-email`, {
      uid,
      token,
    });
    return response.data;
  },

  requestPhoneOtp: async () => {
    const response = await axios.post(
      `${API_BASE}/api/request-phone-otp`,
      {},
      { withCredentials: true }
    );
    return response.data;
  },

  verifyPhone: async (otp: string) => {
    const response = await axios.post(
      `${API_BASE}/api/verify-phone`,
      { otp },
      { withCredentials: true }
    );
    return response.data;
  },

  getUserProfile: async () => {
    const response = await axios.get(`${API_BASE}/api/user/profile`, {
      withCredentials: true,
    });
    return response.data;
  },
};
```

### Example Usage in Next.js Component

```tsx
// app/register/brand/page.tsx
'use client';

import { useState } from 'react';
import { authAPI } from '@/lib/api/auth';

export default function BrandRegistration() {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    accountType: 'brand',
    user: {
      name: '',
      email: '',
      password: '',
      mobileNumber: '',
    },
    organization: {
      name: '',
      officialEmail: '',
      officialPhone: '',
      // ... more fields
    },
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    try {
      const result = await authAPI.register(formData);
      console.log('Registration successful:', result);
      setStep(2); // Move to email verification
    } catch (error) {
      console.error('Registration failed:', error);
    }
  };

  return (
    <div>
      <h1>Register as Brand</h1>
      <form onSubmit={handleSubmit}>
        {/* Form fields */}
      </form>
    </div>
  );
}
```

---

## CORS Configuration

The Drupal backend needs CORS enabled for Next.js to call these APIs. This should already be configured in `services.yml` with the `asm89/stack-cors` package.

**Verify CORS settings in** `cmsapi/web/sites/default/services.yml`:

```yaml
cors.config:
  enabled: true
  allowedOrigins:
    - 'http://localhost:3000'
    - 'https://billoria.ad'
  allowedMethods:
    - GET
    - POST
    - PATCH
    - DELETE
    - OPTIONS
  allowedHeaders:
    - '*'
  exposedHeaders:
    - Content-Type
  maxAge: 3600
  supportsCredentials: true
```

---

## Trust Score Calculation

The trust score starts at 50 and increases with verifications:

- **Base**: 50 points
- **Email Verified**: +10 points (60 total)
- **Phone Verified**: +15 points (75 total)
- **Business Documents Verified**: +25 points (100 total)

---

## Profile Completion Calculation

Profile completion is calculated based on filled fields:

**Common Fields (40%):**
- Organization name, official email, phone
- Division, district, full address

**Optional Fields (30%):**
- Website, business registration, TIN
- Logo, establishment year

**Type-Specific Fields (30%):**
- Brand: budget range, preferred regions
- Agency: services, portfolio size
- Owner: inventory count, coverage districts

---

## Error Handling

All endpoints return consistent error format:

```json
{
  "error": "Error message",
  "details": "Additional context (optional)"
}
```

HTTP Status Codes:
- `200`: Success
- `201`: Created
- `400`: Bad Request (validation error)
- `401`: Unauthorized (invalid token/OTP)
- `403`: Forbidden (access denied)
- `404`: Not Found
- `409`: Conflict (duplicate email)
- `500`: Internal Server Error
